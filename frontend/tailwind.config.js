/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./src/**/*.{html,ts}",
    ],
    theme: {
        screens: {
            'xs': '480px',
            'sm': '576px',   // Tablet Start
            'md': '768px',   // Intermediate Tablet
            'lg': '992px',   // Desktop Start
            'xl': '1400px',  // Large Desktop Start
            '2xl': '1536px',
        },
        extend: {
            colors: {
                // OVERRIDE: Mapping 'blue' to 'red' palette for Mundialito Design without refactoring all templates
                'blue': {
                    50: '#fef2f2',
                    100: '#fee2e2',
                    200: '#fecaca',
                    300: '#fca5a5',
                    400: '#f87171',
                    500: '#ef4444',
                    600: '#dc2626',
                    700: '#b91c1c',
                    800: '#991b1b',
                    900: '#7f1d1d',
                    950: '#450a0a',
                },
                'steel': {
                    300: '#fca5a5', // Red 300
                    400: '#f87171', // Red 400
                    500: '#ef4444', // Red 500
                    600: '#dc2626', // Red 600
                    700: '#b91c1c', // Red 700
                },
                'teal': {
                    200: '#7CB4D4',
                    300: '#5C9DC8',
                    400: '#4682B4',
                    500: '#4682B4',
                    600: '#36648B',
                },
            },
            fontFamily: {
                'orbitron': ['Orbitron', 'monospace'],
                'inter': ['Inter', 'sans-serif'],
            },
            animation: {
                'pulse-glow': 'pulse-glow 2s ease-in-out infinite',
                'blink': 'blink 1s ease-in-out infinite',
                'slide-in': 'slideIn 0.8s ease-out',
            },
            keyframes: {
                'pulse-glow': {
                    '0%, 100%': { boxShadow: '0 0 40px rgba(70, 130, 180, 0.5)' },
                    '50%': { boxShadow: '0 0 80px rgba(70, 130, 180, 0.8)' },
                },
                blink: {
                    '0%, 100%': { opacity: '1' },
                    '50%': { opacity: '0.3' },
                },
                slideIn: {
                    from: { transform: 'translateX(-100%)', opacity: '0' },
                    to: { transform: 'translateX(0)', opacity: '1' },
                },
            },
        },
    },
    plugins: [],
}
