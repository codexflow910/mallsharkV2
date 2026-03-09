const puppeteer = require('puppeteer');

// Path to your Opera GX executable
const operaPath = 'C:\\Users\\pc\\AppData\\Local\\Programs\\Opera GX\\launcher.exe';  // Ensure the path is correct and backslashes are escaped

(async () => {
    // Launch Puppeteer with Opera GX
    const browser = await puppeteer.launch({
        executablePath: operaPath,
        defaultViewport: null,
        args: [
            '--start-maximized',  // Optional, maximizes the browser window
            '--no-sandbox', 
        ]
    });

    const page = await browser.newPage();

    // Navigate to the Temu product page
    await page.goto('https://www.temu.com/mens-knit-solid-color-breathable-lightweight-casual-shoes-comfy-sneakers-for-outdoor-activities-spring-and-autumn-g-601099553055073.html?_oak_mp_inf=EOHC1qqm1ogBGhZmbGFzaF9zYWxlX2xpc3RfbXZqaG5qILOG4MyiMg%3D%3D&top_gallery_url=https%3A%2F%2Fimg.kwcdn.com%2Fproduct%2Ffancy%2F933ed812-5173-4d2f-9e6a-49dc520ab5da.jpg&spec_gallery_id=4127668274&refer_page_sn=10132&refer_source=0&freesia_scene=116&_oak_freesia_scene=116&_oak_rec_ext_1=MTQyODAw&refer_page_el_sn=201401&_x_channel_src=1&_x_channel_scene=spike&_x_sessn_id=50c9m8tr2o&refer_page_name=lightning-deals&refer_page_id=10132_1727274685688_xub6ed25jv', {
        waitUntil: 'networkidle2',  // Wait until the page is fully loaded
    });

    // Wait for CAPTCHA to be solved manually
    console.log('Please solve the CAPTCHA in the browser window...');
    await page.waitForTimeout(30000);  // Adjust timeout as needed for manual CAPTCHA solving

    // After CAPTCHA is solved, scrape the page content
    const productDetails = await page.evaluate(() => {
        const title = document.querySelector('h1')?.innerText || 'Title not found';
        const price = document.querySelector('.price--big')?.innerText || 'Price not found';
        const description = document.querySelector('.product-description')?.innerText || 'Description not found';
        return { title, price, description };
    });

    console.log('Product Details:', productDetails);

    // Close the browser
    await browser.close();
})();
