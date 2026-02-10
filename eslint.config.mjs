// eslint.config.mjs
import js from "@eslint/js";
import pluginImport from "eslint-plugin-import";
import pluginUnused from "eslint-plugin-unused-imports";
import pluginSimpleSort from "eslint-plugin-simple-import-sort";
import pluginHtml from "eslint-plugin-html";

/** @type {import('eslint').Linter.FlatConfig[]} */
export default [
    // 1) Глобальные игноры
    {
        ignores: [
            "vendor/**",
            "node_modules/**",
            "public/**",
            "storage/**",
            "bootstrap/**",
        ],
    },

    // 2) JS-файлы (resources/js)
    {
        files: ["resources/js/**/*.{js,mjs,cjs}"],
        languageOptions: {
            ecmaVersion: 2022,
            sourceType: "module",
            globals: {
                window: "readonly",
                document: "readonly",
            },
        },
        plugins: {
            import: pluginImport,
            "unused-imports": pluginUnused,
            "simple-import-sort": pluginSimpleSort,
        },
        rules: {
            // Базовый набор правил JS от ESLint
            ...js.configs.recommended.rules,

            // Наши дополнительные правила:
            "no-console": "warn",
            "no-debugger": "warn",

            "unused-imports/no-unused-imports": "error",
            "simple-import-sort/imports": "warn",
            "simple-import-sort/exports": "warn",
        },
    },

    // 3) JS внутри Blade/HTML
    {
        files: ["resources/views/**/*.blade.php", "resources/**/*.html"],
        plugins: {
            html: pluginHtml,
        },
        languageOptions: {
            ecmaVersion: 2022,
            sourceType: "module",
        },
        rules: {
            ...js.configs.recommended.rules,
        },
    },
];
