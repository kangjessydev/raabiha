const puppeteer = require('puppeteer');

(async () => {
  const browser = await puppeteer.launch({ args: ['--no-sandbox'] });
  const page = await browser.newPage();
  
  page.on('console', msg => console.log('PAGE LOG:', msg.text()));
  page.on('pageerror', error => console.log('PAGE ERROR:', error.message));

  await page.goto('http://127.0.0.1:8000/product/monolith-overcoat', { waitUntil: 'networkidle0' });
  
  const title = await page.title();
  console.log("PAGE TITLE:", title);
  
  const productName = await page.$eval('#product-name', el => el.textContent).catch(() => 'NOT FOUND');
  console.log("PRODUCT NAME:", productName);

  await browser.close();
})();
