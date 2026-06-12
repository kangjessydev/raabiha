<div class="flex flex-col gap-1.5 py-0.5" style="align-items: flex-start;">
    <div class="font-bold text-gray-900 dark:text-white" style="font-size: 14px; line-height: 20px;">
        {{ $getRecord()->name }}
    </div>
    <div onclick="
             event.stopPropagation();
             event.preventDefault();
             const code = '{{ $getRecord()->code }}';
             try {
                 if (navigator.clipboard && window.isSecureContext) {
                     navigator.clipboard.writeText(code);
                 } else {
                     var textarea = document.createElement('textarea');
                     textarea.value = code;
                     textarea.style.position = 'fixed';
                     textarea.style.opacity = '0';
                     document.body.appendChild(textarea);
                     textarea.select();
                     document.execCommand('copy');
                     document.body.removeChild(textarea);
                 }
             } catch (err) {
                 console.error(err);
             }
             const badge = this;
             const label = badge.querySelector('.code-label');
             const svgNormal = badge.querySelector('.svg-normal');
             const svgSuccess = badge.querySelector('.svg-success');
             
             label.innerText = 'Disalin!';
             badge.style.borderColor = '#10b981';
             badge.style.backgroundColor = '#ecfdf5';
             label.style.color = '#10b981';
             if (svgNormal) svgNormal.style.setProperty('display', 'none', 'important');
             if (svgSuccess) svgSuccess.style.setProperty('display', 'inline-block', 'important');
             
             setTimeout(() => {
                 label.innerText = code;
                 badge.style.borderColor = '';
                 badge.style.backgroundColor = '';
                 label.style.color = '';
                 if (svgNormal) svgNormal.style.setProperty('display', 'inline-block', 'important');
                 if (svgSuccess) svgSuccess.style.setProperty('display', 'none', 'important');
             }, 1500);
         "
         class="hover:bg-primary-50 dark:hover:bg-primary-950/20 hover:border-primary-300 dark:hover:border-primary-800 transition-all duration-200"
         style="
             display: inline-flex;
             align-items: center;
             gap: 6px;
             padding: 2px 8px;
             font-size: 11px;
             color: #4b5563;
             background-color: rgba(249, 250, 251, 0.8);
             border: 1px solid #e5e7eb;
             border-radius: 6px;
             cursor: pointer;
             width: max-content;
             max-width: max-content;
             position: relative;
             z-index: 10;
             pointer-events: auto;
         "
         title="Klik untuk menyalin kode">
        <svg class="svg-normal" style="width: 14px; height: 14px; color: #9ca3af; flex-shrink: 0; display: inline-block;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H5.25m11.9-3.664A2.251 2.251 0 0015 2.25h-3a2.251 2.251 0 00-2.15 1.586m5.8 0c.065.21.1.433.1.664v.75h-6V4.5c0-.231.035-.454.1-.664M6.75 7.5H4.875c-.621 0-1.125.504-1.125 1.125v12c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V16.5m-3-3H18a2.25 2.25 0 002.25-2.25V5.25A2.25 2.25 0 0018 3h-2.25m-7.5 10.5H18m-7.5-3H18" />
        </svg>
        <svg class="svg-success" style="width: 14px; height: 14px; color: #10b981; flex-shrink: 0; display: none;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
        </svg>
        <span class="code-label font-mono font-semibold tracking-wider" style="font-family: monospace; font-size: 11px;">{{ $getRecord()->code }}</span>
    </div>
</div>
