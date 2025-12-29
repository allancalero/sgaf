// Script para agregar dark mode a las páginas que faltan
const fs = require('fs');

const files = [
    'c:/xampp/htdocs/sgaf2/resources/js/Pages/Activos/Reportes.vue',
    'c:/xampp/htdocs/sgaf2/resources/js/Pages/Activos/Depreciacion.vue',
    'c:/xampp/htdocs/sgaf2/resources/js/Pages/Activos/Trazabilidad.vue',
    'c:/xampp/htdocs/sgaf2/resources/js/Pages/Activos/EtiquetasQr.vue'
];

const replacements = [
    { from: ' text-gray-800"', to: ' text-gray-800 dark:text-gray-100"' },
    { from: ' text-gray-900"', to: ' text-gray-900 dark:text-gray-100"' },
    { from: ' text-gray-500"', to: ' text-gray-500 dark:text-gray-400"' },
    { from: ' text-gray-600"', to: ' text-gray-600 dark:text-gray-400"' },
    { from: ' text-gray-700"', to: ' text-gray-700 dark:text-gray-300"' },
    { from: ' bg-white p-', to: ' bg-white p- dark:bg-gray-800 ' },
    { from: ' bg-white shadow-', to: ' bg-white dark:bg-gray-800 shadow-' },
    { from: 'border-gray-200 bg-white', to: 'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800' },
    { from: 'border-gray-100 bg-white', to: 'border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800' },
    { from: 'bg-gray-50 text-xs', to: 'bg-gray-50 dark:bg-gray-900 text-xs dark:text-gray-400' },
    { from: 'border-gray-100 hover:bg-gray-50', to: 'border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50' },
];

files.forEach(file => {
    try {
        let content = fs.readFileSync(file, 'utf8');
        let changed = false;

        replacements.forEach(({ from, to }) => {
            if (content.includes(from) && !content.includes(to)) {
                content = content.split(from).join(to);
                changed = true;
            }
        });

        if (changed) {
            fs.writeFileSync(file, content, 'utf8');
            console.log(`✅ ${file}`);
        } else {
            console.log(`⏭️  ${file} - Sin cambios`);
        }
    } catch (err) {
        console.error(`❌ ${file}: ${err.message}`);
    }
});

console.log('\nDark mode aplicado!');
