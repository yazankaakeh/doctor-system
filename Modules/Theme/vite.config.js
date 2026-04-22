/*
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import html from '@rollup/plugin-html';
import { glob } from 'glob';
import path from 'path';
import iconsPlugin from './vite.icons.plugin.js';

/!**
 * Get Files from a directory
 * @param {string} query
 * @returns array
 *!/
function GetFilesArray(query) {
  return glob.sync(query);
}

// Page JS Files
const pageJsFiles = GetFilesArray('resources/assets/js/!*.js');

// Processing Vendor JS Files
const vendorJsFiles = GetFilesArray('resources/assets/vendor/js/!*.js');

// Processing Libs JS Files
const LibsJsFiles = GetFilesArray('resources/assets/vendor/libs/!**!/!*.js');

// Processing Libs Scss & Css Files
const LibsScssFiles = GetFilesArray('resources/assets/vendor/libs/!**!/!(_)*.scss');
const LibsCssFiles = GetFilesArray('resources/assets/vendor/libs/!**!/!*.css');

// Processing Core, Themes & Pages Scss Files
const CoreScssFiles = GetFilesArray('resources/assets/vendor/scss/!**!/!(_)*.scss');

// Processing Fonts Scss & JS Files
const FontsScssFiles = GetFilesArray('resources/assets/vendor/fonts/!(_)*.scss');
const FontsJsFiles = GetFilesArray('resources/assets/vendor/fonts/!**!/!(_)*.js');
const FontsCssFiles = GetFilesArray('resources/assets/vendor/fonts/!**!/!(_)*.css');

// Processing Window Assignment for Libs like jKanban, pdfMake
function libsWindowAssignment() {
  return {
    name: 'libsWindowAssignment',

    transform(src, id) {
      if (id.includes('jkanban.js')) {
        return src.replace('this.jKanban', 'window.jKanban');
      } else if (id.includes('vfs_fonts')) {
        return src.replaceAll('this.pdfMake', 'window.pdfMake');
      }
    }
  };
}

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/assets/css/demo.css',
        'resources/js/app.js',
        ...pageJsFiles,
        ...vendorJsFiles,
        ...LibsJsFiles,
        'resources/js/laravel-user-management.js', // Processing Laravel User Management CRUD JS File
        ...CoreScssFiles,
        ...LibsScssFiles,
        ...LibsCssFiles,
        ...FontsScssFiles,
        ...FontsJsFiles,
        ...FontsCssFiles
      ],
      refresh: true
    }),
    html(),
    libsWindowAssignment(),
    iconsPlugin()
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources')
    }
  },
  json: {
    stringify: true // Helps with JSON import compatibility
  },
  build: {
    commonjsOptions: {
      include: [/node_modules/] // Helps with importing CommonJS modules
    }
  }
});
*/


/*
import {defineConfig} from 'vite'
import laravel from 'laravel-vite-plugin'
import path from 'path'

export default defineConfig({
    root: __dirname,
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                // لو تبي تضيف ملف SCSS كمدخل مستقل لازم تضيفه هنا أو (أفضل) تستورده داخل app.css
                // 'resources/assets/vendor/libs/@form-validation/form-validation.scss',
            ],
            publicDirectory: path.resolve(__dirname, '../../public'),
            // 👇 لازم يطابق الوسيط الثاني في @vite
            buildDirectory: 'build/modules/theme',
            hotFile: path.resolve(__dirname, '../../storage/framework/vite.hot'),
            refresh: ['resources/!**', '../../resources/views/!**', '../../routes/!**', '../../app/Http/!**'],
        }),
    ],
    resolve: {
        alias: {
            '@theme': path.resolve(__dirname, 'resources/js'),
            '@theme-styles': path.resolve(__dirname, 'resources/assets/css'),
        },
    },
    build: {
        // 👇 لازم يطابق buildDirectory: manifest سيُكتب هنا
        outDir: path.resolve(__dirname, '../../public/build/modules/theme'),
        emptyOutDir: true,
        manifest: true,
    },
})

*/

/*import {defineConfig} from 'vite'
import laravel from 'laravel-vite-plugin'
import {globSync} from 'glob'
import path from 'path'
import iconsPlugin from './vite.icons.plugin.js'

// دالة تساعدنا نجلب ملفات مطابقة للنمط
function getFiles(pattern) {
    return globSync(pattern, {nodir: true})
}

// ✅ صحّحنا الأنماط: استخدم * و **!/!* (بدون علامات تعجب)
// Page JS Files
const pageJsFiles = getFiles('resources/assets/js/!*.js')

// Vendor JS
const vendorJsFiles = getFiles('resources/assets/vendor/js/!*.js')

// Libs JS
const libsJsFiles = getFiles('resources/assets/vendor/libs/!**!/!*.js')

// Libs SCSS & CSS
const libsScssFiles = getFiles('resources/assets/vendor/libs/!**!/!(_)*.scss')
const libsCssFiles = getFiles('resources/assets/vendor/libs/!**!/!*.css')

// Core/Themes/Pages SCSS
const coreScssFiles = getFiles('resources/assets/vendor/scss/!**!/!(_)*.scss')

// Fonts SCSS/JS/CSS
const fontsScssFiles = getFiles('resources/assets/vendor/fonts/!(_)*.scss')
const fontsJsFiles = getFiles('resources/assets/vendor/fonts/!**!/!(_)*.js')
const fontsCssFiles = getFiles('resources/assets/vendor/fonts/!**!/!(_)*.css')

// معالجة بعض المكتبات التي تحتاج window assignment
function libsWindowAssignment() {
    return {
        name: 'libsWindowAssignment',
        transform(src, id) {
            if (id.includes('jkanban.js')) {
                return src.replace('this.jKanban', 'window.jKanban')
            } else if (id.includes('vfs_fonts')) {
                return src.replaceAll('this.pdfMake', 'window.pdfMake')
            }
        }
    }
}

export default defineConfig({
    // نشغّل Vite من داخل الموديول
    root: __dirname,

    plugins: [
        laravel({
            // 👇 نقاط الدخول: أبقيناها كثيرة مثل ما تريد، مع إضافة ملف SCSS الذي تستدعيه من Blade
            input: [
                'resources/css/app.css',
                'resources/assets/css/demo.css',
                'resources/js/app.js',
                ...pageJsFiles,
                ...vendorJsFiles,
                ...libsJsFiles,
                'resources/js/laravel-user-management.js',
                ...coreScssFiles,
                ...libsScssFiles,
                ...libsCssFiles,
                ...fontsScssFiles,
                ...fontsJsFiles,
                ...fontsCssFiles,
                // ⚠️ مهم: حتى يشتغل @vite لهذا الملف تحديداً
                'resources/assets/vendor/libs/@form-validation/form-validation.scss',
            ],

            refresh: true,

            // هذه المسارات بالنسبة لجذر مشروع لارفيل (خارج الموديول)
            publicDirectory: path.resolve(__dirname, '../../public'),
            hotFile: path.resolve(__dirname, '../../storage/framework/vite.hot'),

            // 👈 لازم يطابق وسيط @vite في Blade
            buildDirectory: 'build/modules/theme',
        }),
        libsWindowAssignment(),
        iconsPlugin(),
    ],

    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources'),
        }
    },

    json: {
        stringify: true
    },

    build: {
        outDir: path.resolve(__dirname, '../../public/build/modules/theme'),
        manifest: true,
        emptyOutDir: true,                                                // ← مهم جدًا
        commonjsOptions: {include: [/node_modules/]},
    },
})*/

// Modules/Theme/vite.config.js
import {defineConfig} from 'vite'
import laravel from 'laravel-vite-plugin'
import html from '@rollup/plugin-html'
import {globSync} from 'glob'
import {join, resolve} from 'node:path'
import {copyFileSync, existsSync, mkdirSync} from 'node:fs'
import iconsPlugin from './vite.icons.plugin.js'

// جمع الملفات بنمط glob
const pick = (p) => globSync(p, {nodir: true})

// نفس الفكرة التي تريدها
// NOTE: moment.min.js is excluded because its minified source contains
// `require("./locale/" + e)` which esbuild cannot statically resolve
// (breaks every Vite build). The file is a self-contained vendor script
// and is never imported as a module, so dropping it from the input list
// has no runtime impact; blades that need moment already load it via
// <script> or through fullcalendar / other bundled libs.
const EXCLUDED_PAGE_JS = new Set(['moment.min.js'])
const pageJsFiles = pick('resources/assets/js/*.js')
    .filter((f) => !EXCLUDED_PAGE_JS.has(f.split(/[\\/]/).pop()))
const vendorJsFiles = pick('resources/assets/vendor/js/*.js')
const libsJsFiles = pick('resources/assets/vendor/libs/**/*.js')

const libsScssFiles = pick('resources/assets/vendor/libs/**/!(_)*.scss')
const libsCssFiles = pick('resources/assets/vendor/libs/**/*.css')

const coreScssFiles = pick('resources/assets/vendor/scss/**/!(_)*.scss')

const fontsScss = pick('resources/assets/vendor/fonts/!(_)*.scss')
const fontsJs = pick('resources/assets/vendor/fonts/**/!(_)*.js')
const fontsCss = pick('resources/assets/vendor/fonts/**/!(_)*.css')

// CMS Module Assets
const cmsJsFiles = pick('../CMS/resources/assets/js/*.js')
// Blog Module Assets
const blogJsFiles = pick('../Blog/resources/assets/js/*.js')

// بعض المكتبات تحتاج window assignment
function libsWindowAssignment() {
    return {
        name: 'libsWindowAssignment',
        transform(src, id) {
            if (id.includes('jkanban.js')) {
                return src.replace('this.jKanban', 'window.jKanban')
            } else if (id.includes('vfs_fonts')) {
                return src.replaceAll('this.pdfMake', 'window.pdfMake')
            }
        }
    }
}

// ينقل manifest من .vite إلى جذر outDir
function moveManifestUp() {
    return {
        name: 'move-manifest-up',
        closeBundle() {
            const outDir = resolve(__dirname, '../../public/build/modules/theme')
            const src = join(outDir, '.vite', 'manifest.json')
            const dest = join(outDir, 'manifest.json')
            if (existsSync(src)) {
                // تأكد وجود المجلد المستهدف
                if (!existsSync(outDir)) mkdirSync(outDir, {recursive: true})
                copyFileSync(src, dest)
            }
        }
    }
}

export default defineConfig({
    root: __dirname, // نشغّل Vite داخل الموديول

    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/assets/css/demo.css',
                'resources/js/app.js',
                ...pageJsFiles,
                ...vendorJsFiles,
                ...libsJsFiles,
                'resources/js/laravel-user-management.js',
                ...coreScssFiles,
                ...libsScssFiles,
                ...libsCssFiles,
                ...fontsScss,
                ...fontsJs,
                ...fontsCss,
                // بما أنك تستدعي هذا الملف مباشرةً من Blade لازم يكون ضمن input
                'resources/assets/vendor/libs/@form-validation/form-validation.scss',
                // CMS Module Assets
                ...cmsJsFiles,
                // Blog Module Assets
                ...blogJsFiles,
            ],
            refresh: true,
            publicDirectory: resolve(__dirname, '../../public'),
            hotFile: resolve(__dirname, '../../storage/framework/vite.hot'),
            // لازم يطابق وسيط @vite
            buildDirectory: 'build/modules/theme',
        }),
        html(),
        libsWindowAssignment(),
        iconsPlugin(),
        moveManifestUp(), // ← ينقل manifest إلى المكان الذي يتوقعه Laravel
    ],

    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources'),
        }
    },

    json: {stringify: true},

    build: {
        // Laravel يتوقع manifest هنا لأنك تستخدم @vite(..., 'build/modules/theme')
        outDir: resolve(__dirname, '../../public/build/modules/theme'),
        emptyOutDir: true,
        manifest: true,
        commonjsOptions: {include: [/node_modules/]},
    },
})
