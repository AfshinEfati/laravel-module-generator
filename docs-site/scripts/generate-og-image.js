import puppeteer from 'puppeteer';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

(async () => {
  console.log('🚀 Generating OG image...');
  
  const browser = await puppeteer.launch({
    headless: 'new',
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });
  
  const page = await browser.newPage();
  await page.setViewport({ width: 1200, height: 630, deviceScaleFactor: 2 });
  
  const htmlPath = join(__dirname, '..', 'og-image-template.html');
  await page.goto(`file://${htmlPath}`, { waitUntil: 'networkidle0' });
  
  const outputPath = join(__dirname, '..', 'public', 'og-image.png');
  await page.screenshot({
    path: outputPath,
    clip: { x: 0, y: 0, width: 1200, height: 630 }
  });
  
  await browser.close();
  
  console.log('✅ OG image generated successfully at:', outputPath);
})();
