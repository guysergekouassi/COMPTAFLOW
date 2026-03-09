const fs = require('fs');

const filePath = 'c:\\laragon\\www\\COMPTAFLOW\\RAPPORT-BIEN-FIXED.html';
if (!fs.existsSync(filePath)) {
    console.error('Fichier non trouvé:', filePath);
    process.exit(1);
}

let content = fs.readFileSync(filePath, 'utf8');

// 1. Agrandir le logo Leader (Ligne 640 environ)
// On cherche l'image avec alt="Entreprise Leader"
const logoRegex = /(<img[^>]+alt="Entreprise Leader"[^>]+style=")(max-height:80px;)/;
if (logoRegex.test(content)) {
    content = content.replace(logoRegex, '$1max-height:120px;');
    console.log('Logo Leader : Redimensionné à 120px.');
} else {
    console.log('Logo Leader : Cible non trouvée (regex).');
}

// 2. Encadrer "RAPPORT DE STAGE DE FIN DE CYCLE" (Ligne 650 environ)
// On cherche le h4 contenant ce texte
const titleRegex = /(<h4[^>]+style=")([^"]+)(">[\s\n]+RAPPORT DE STAGE DE FIN DE CYCLE<\/h4>)/;
if (titleRegex.test(content)) {
    // On ajoute le border et le padding au style existant
    content = content.replace(titleRegex, '$1$2 border: 2px solid #333; padding: 10px; display: inline-block; margin-top: 10px;$3');
    console.log('Titre 1 : Encadré.');
} else {
    console.log('Titre 1 : Cible non trouvée (regex).');
}

// 3. Encadrer "Filière : ... (IDA)" (Lignes 651-653)
const filiereRegex = /(Filière : <span class="bold" style="color:var\(--primary\);">Informatique[\s\n]+Développeur d'Application \(IDA\)<\/span>)/;
if (filiereRegex.test(content)) {
    content = content.replace(filiereRegex, '<span style="border: 1px solid #666; padding: 5px; display: inline-block; margin-top: 5px;">$1</span>');
    console.log('Filière : Encadrée.');
} else {
    console.log('Filière : Cible non trouvée (regex).');
}

// 4. Supprimer le bloc Directeur (Lignes 682-686)
const directorRegex = /<div class="jury-column" style="text-align:right;">[\s\S]*?<h5>Directeur de Mémoire :<\/h5>[\s\S]*?<\/div>/;
if (directorRegex.test(content)) {
    content = content.replace(directorRegex, '');
    console.log('Bloc Directeur : Supprimé.');
} else {
    console.log('Bloc Directeur : Cible non trouvée (regex).');
}

// Sauvegarde
fs.writeFileSync(filePath, content, 'utf8');
console.log('Traitement terminé.');
