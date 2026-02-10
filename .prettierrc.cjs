/** @type {import('prettier').Config} */
module.exports = {
    semi: true,
    singleQuote: true,
    trailingComma: "all",
    printWidth: 100,
    tabWidth: 4,
    useTabs: false,

    plugins: ["prettier-plugin-tailwindcss", "prettier-plugin-blade"],

    overrides: [
        {
            files: "*.blade.php",
            options: {
                parser: "blade",
            },
        },
    ],
};
