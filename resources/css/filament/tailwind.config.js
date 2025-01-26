import preset from '../../../vendor/filament/filament/tailwind.config.preset'

import defaultTheme from 'tailwindcss/defaultTheme';

export default {
    presets: [preset],
    content: [
        './app/Filament/app/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './resources/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            typography: {
              DEFAULT: {
                css: {
                  maxWidth: '100ch', // add required value here
                }
              }
            }
        },
    },
    plugins: [

    ],
}
