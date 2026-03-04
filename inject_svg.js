const fs = require('fs');

const svgContent = fs.readFileSync('mcd_v3.svg', 'utf8');
const htmlContent = fs.readFileSync('rapport_de_stage.html', 'utf8');

const startIndex = htmlContent.indexOf('<svg viewBox=\"0 0 900 720\"');
const endIndex = htmlContent.indexOf('</svg>', startIndex) + 6;

if (startIndex !== -1 && endIndex !== -1) {
    const newHtml = htmlContent.substring(0, startIndex) + svgContent + htmlContent.substring(endIndex);
    fs.writeFileSync('rapport_de_stage.html', newHtml, 'utf8');
    console.log('SVG replaced successfully');
} else {
    console.log('SVG boundaries not found');
}
