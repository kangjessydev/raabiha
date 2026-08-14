import zipfile
import xml.etree.ElementTree as ET
import sys

def parse_xlsx(filename):
    with zipfile.ZipFile(filename) as z:
        # Load shared strings
        shared_strings = []
        if 'xl/sharedStrings.xml' in z.namelist():
            tree = ET.fromstring(z.read('xl/sharedStrings.xml'))
            ns = {'main': 'http://schemas.openxmlformats.org/spreadsheetml/2006/main'}
            for si in tree.findall('main:si', ns):
                # get all text inside si
                t = "".join([node.text for node in si.iter() if node.text])
                shared_strings.append(t)
        
        # Load sheet names from workbook.xml
        wb_tree = ET.fromstring(z.read('xl/workbook.xml'))
        ns = {'main': 'http://schemas.openxmlformats.org/spreadsheetml/2006/main',
              'r': 'http://schemas.openxmlformats.org/officeDocument/2006/relationships'}
        
        sheets = []
        for s in wb_tree.find('main:sheets', ns).findall('main:sheet', ns):
            sheets.append((s.attrib['name'], s.attrib['sheetId'], s.attrib.get('{http://schemas.openxmlformats.org/officeDocument/2006/relationships}id')))
        
        # Get rels to find file for each sheet
        rels_tree = ET.fromstring(z.read('xl/_rels/workbook.xml.rels'))
        rel_ns = {'r': 'http://schemas.openxmlformats.org/package/2006/relationships'}
        rel_map = {}
        for rel in rels_tree.findall('{http://schemas.openxmlformats.org/package/2006/relationships}Relationship'):
            rel_map[rel.attrib['Id']] = rel.attrib['Target']
        
        print("=== SHEETS FOUND IN WORKBOOK ===")
        for name, sid, rId in sheets:
            target = rel_map.get(rId, '')
            sheet_file = 'xl/' + target if not target.startswith('xl/') else target
            print(f"Sheet Name: '{name}' -> File: {sheet_file}")
            
            if sheet_file in z.namelist():
                s_tree = ET.fromstring(z.read(sheet_file))
                sheetData = s_tree.find('main:sheetData', ns)
                if sheetData is None:
                    continue
                
                rows_output = []
                for row in sheetData.findall('main:row', ns):
                    r_num = row.attrib['r']
                    row_cells = []
                    for c in row.findall('main:c', ns):
                        cell_ref = c.attrib['r']
                        cell_type = c.attrib.get('t', '')
                        
                        f_val = c.find('main:f', ns)
                        v_val = c.find('main:v', ns)
                        
                        formula = f_val.text if f_val is not None and f_val.text else ""
                        val = v_val.text if v_val is not None and v_val.text else ""
                        
                        if cell_type == 's' and val != "":
                            val_str = shared_strings[int(val)] if int(val) < len(shared_strings) else val
                        else:
                            val_str = val
                            
                        if formula:
                            row_cells.append(f"{cell_ref}: ={formula} [CachedVal: {val_str}]")
                        elif val_str:
                            row_cells.append(f"{cell_ref}: {val_str}")
                    
                    if row_cells:
                        rows_output.append(f"Row {r_num}: " + " | ".join(row_cells))
                
                # Print non-empty sheets or key sheets
                print(f"--- CONTENT FOR '{name}' ({len(rows_output)} non-empty rows) ---")
                for r_line in rows_output[:50]: # Print first 50 rows
                    print(r_line)
                if len(rows_output) > 50:
                    print(f"... ({len(rows_output)-50} more rows truncated)")
                print("\n")

if __name__ == '__main__':
    filename = sys.argv[1] if len(sys.argv) > 1 else 'Akuntansi Raabiha 2026.xlsx'
    parse_xlsx(filename)
