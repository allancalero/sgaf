/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./src/**/*.{html,ts}",
    ],
    theme: {
        extend: {
            colors: {
                'steel': {
                    300: '#7CB4D4',
                    400: '#5C9DC8',
                    500: '#4682B4',
                    600: '#36648B',
                    700: '#2A4F6E',
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
