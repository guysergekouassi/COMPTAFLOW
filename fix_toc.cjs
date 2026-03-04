const fs = require('fs');

const file = 'rapport_de_stage.html';
let html = fs.readFileSync(file, 'utf8');

// fix the first wrong item
const wrongTitleRegex = /<li class="toc-level-2" style="margin-left:20px;"><span class="title">CONCEPTION ET DÉVELOPPEMENT D'UNE APPLICATION DE GESTION COMPTABLE POUR[\s\S]*?L'OPTIMISATION DES OPÉRATIONS FINANCIÈRES : CAS DE LEADER WORLD PERFECT SARL<\/span><span class="page-dots"><\/span><span class="page-num">...<\/span><\/li>/;
html = html.replace(wrongTitleRegex, '');

// fix conclusion
html = html.replace(/<li class="toc-level-1"><span class="title">C\s*CONCLUSION GÉNÉRALE<\/span><span class="page-dots"><\/span><span class="page-num">\.\.\.<\/span><\/li>/g, '<li class="toc-level-1"><span class="title">CONCLUSION GÉNÉRALE</span><span class="page-dots"></span><span class="page-num">...</span></li>');

// fix perspectives
html = html.replace(/<li class="toc-level-1"><span class="title">P\s*PERSPECTIVES<\/span><span class="page-dots"><\/span><span class="page-num">\.\.\.<\/span><\/li>/g, '<li class="toc-level-1"><span class="title">PERSPECTIVES</span><span class="page-dots"></span><span class="page-num">...</span></li>');

fs.writeFileSync(file, html, 'utf8');
console.log("FIXED MISTAKES IN TOC");
