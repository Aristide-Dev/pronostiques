const puppeteer = require('puppeteer');
const iconv = require('iconv-lite');

(async () => {
  const browser = await puppeteer.launch({ headless: true });
  const page = await browser.newPage();
  
  // Définir des en-têtes pour imiter un vrai navigateur
  await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36');
  await page.setExtraHTTPHeaders({
    'Accept-Language': 'fr-FR,fr;q=0.9',
  });

  try {
    await page.goto('https://www.forebet.com/fr/pronostics-pour-aujourd-hui', { waitUntil: 'networkidle2' });

    // Attendre que les éléments de pronostic soient chargés
    await page.waitForSelector('.rcnt.tr_0');

    const matches = await page.evaluate(() => {
      const rows = document.querySelectorAll('.rcnt.tr_0');
      let data = [];
      rows.forEach(row => {
        const homeTeam = row.querySelector('.homeTeam') ? row.querySelector('.homeTeam').innerText.trim() : '';
        const awayTeam = row.querySelector('.awayTeam') ? row.querySelector('.awayTeam').innerText.trim() : '';
        const prediction = row.querySelector('.forebet') ? row.querySelector('.forebet').innerText.trim() : '';
        const probability = row.querySelector('.prob') ? row.querySelector('.prob').innerText.trim() : '';
        if (homeTeam && awayTeam) {
          data.push({
            teams: `${homeTeam} vs ${awayTeam}`,
            prediction: prediction,
            probability: probability.replace('%', ''),
          });
        }
      });
      return data;
    });

    // Convertir les données en UTF-8 (par défaut, Node.js utilise UTF-8)
    const utf8Data = iconv.encode(JSON.stringify(matches, null, 2), 'utf-8');

    // Afficher les données
    console.log(utf8Data.toString());
  } catch (error) {
    console.error('Erreur lors du scraping:', error);
  } finally {
    await browser.close();
  }
})();
