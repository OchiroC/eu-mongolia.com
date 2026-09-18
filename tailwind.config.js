import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import animate from 'tailwindcss-animate';

/**
 * Дизайны систем — "Нисэх" (нисэх буудлын чиглүүлэх тэмдэгжүүлэлт).
 *
 * Гурван дүрэм:
 *   1. Сүүдэр байхгүй — зөвхөн 1px үсэн зураас (hairline).
 *   2. Градиент, шил (glass), бүдгэрүүлэлт байхгүй — хавтгай гадаргуу.
 *   3. Өнгө хэмнэнэ — цаас, бэх, нэг дохио (signal).
 */
const NEUTRAL = {
    50: '#f8f8f7',
    100: '#f1f1ef',
    200: '#e4e4e0',
    300: '#cfcec9',
    400: '#9c9b94',
    500: '#6f6e68',
    600: '#55544f',
    700: '#3d3c38',
    800: '#262624',
    900: '#1b1b19',
    950: '#0d0d0c',
};

export default {
    darkMode: ['class'],
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                // IBM Plex — техник шинжтэй, кирилл үсгийн бүрэн дэмжлэгтэй.
                sans: ['"IBM Plex Sans"', ...defaultTheme.fontFamily.sans],
                mono: ['"IBM Plex Mono"', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                /*
                 * brand — 80 файлд 522 удаа хэрэглэгдсэн тул нэрийг нь хадгалж,
                 * утгыг нь бэхэн хар болгон сольсон. Ингэснээр апп бүхэлдээ
                 * нэг дор шинэ өнгөнд шилжинэ. Үндсэн үйлдлийн товч = хар.
                 */
                brand: {
                    50: '#f7f7f6',
                    100: '#eeeeeb',
                    200: '#dddcd8',
                    300: '#bcbbb5',
                    400: '#8f8e87',
                    500: '#5f5f59',
                    600: '#1b1b19',
                    700: '#131312',
                    800: '#0d0d0c',
                    900: '#070706',
                    950: '#000000',
                },
                // signal — цорын ганц анхаарал татах өнгө (самбарын шар).
                signal: {
                    50: '#fff8e6',
                    100: '#ffeec2',
                    200: '#ffdd85',
                    300: '#ffcb47',
                    400: '#ffb81c',
                    500: '#f59e00',
                    600: '#c47a00',
                    700: '#8f5800',
                },
                // board — хар самбарын гадаргуу (нислэгийн самбар).
                board: {
                    DEFAULT: '#0b0c0e',
                    soft: '#15171a',
                    line: '#272a2f',
                },
                /*
                 * gray, slate — Tailwind-ийн хүйтэн саарлыг бэхэн палитртай нийцэх
                 * дулаан саарлаар сольсон (994 хэрэглээ нэг дор нийцнэ).
                 */
                gray: NEUTRAL,
                slate: NEUTRAL,
                // Цаас, бэхний саарлууд.
                sand: {
                    50: '#fbfbfa',
                    100: '#f4f4f2',
                    200: '#e8e8e4',
                },
                // shadcn-vue токенууд (CSS хувьсагчид app.css дотор).
                border: 'hsl(var(--border))',
                input: 'hsl(var(--input))',
                ring: 'hsl(var(--ring))',
                background: 'hsl(var(--background))',
                foreground: 'hsl(var(--foreground))',
                primary: {
                    DEFAULT: 'hsl(var(--primary))',
                    foreground: 'hsl(var(--primary-foreground))',
                },
                secondary: {
                    DEFAULT: 'hsl(var(--secondary))',
                    foreground: 'hsl(var(--secondary-foreground))',
                },
                destructive: {
                    DEFAULT: 'hsl(var(--destructive))',
                    foreground: 'hsl(var(--destructive-foreground))',
                },
                muted: {
                    DEFAULT: 'hsl(var(--muted))',
                    foreground: 'hsl(var(--muted-foreground))',
                },
                accent: {
                    DEFAULT: 'hsl(var(--accent))',
                    foreground: 'hsl(var(--accent-foreground))',
                },
                popover: {
                    DEFAULT: 'hsl(var(--popover))',
                    foreground: 'hsl(var(--popover-foreground))',
                },
                card: {
                    DEFAULT: 'hsl(var(--card))',
                    foreground: 'hsl(var(--card-foreground))',
                },
            },
            /*
             * Радиусыг бүхэлд нь хатууруулсан. rounded-2xl (126 хэрэглээ) 16px→8px,
             * rounded-3xl 24px→10px болсноор апп даяар дугуйрал багасна.
             */
            borderRadius: {
                sm: 'calc(var(--radius) - 2px)',
                md: 'calc(var(--radius) - 1px)',
                lg: 'var(--radius)',
                xl: 'calc(var(--radius) + 2px)',
                '2xl': 'calc(var(--radius) + 4px)',
                '3xl': 'calc(var(--radius) + 6px)',
            },
            /*
             * Сүүдрийг үсэн зураас болгон сольсон. Хуучин нэрсийг хадгалсан тул
             * 100 гаруй хэрэглээ эвдрэлгүйгээр хавтгай болно. Hover-т зөвхөн
             * зураасны өнгө гүнзгийрнэ.
             */
            boxShadow: {
                // Tailwind-ийн үндсэн сүүдэр → үсэн зураас. lg/xl нь зөвхөн хөвөгч давхаргад бага гүн өгнө.
                sm: '0 0 0 1px rgba(15, 17, 21, 0.06)',
                DEFAULT: '0 0 0 1px rgba(15, 17, 21, 0.08)',
                md: '0 0 0 1px rgba(15, 17, 21, 0.10)',
                lg: '0 0 0 1px rgba(15, 17, 21, 0.10), 0 8px 24px -12px rgba(15, 17, 21, 0.18)',
                xl: '0 0 0 1px rgba(15, 17, 21, 0.10), 0 12px 32px -14px rgba(15, 17, 21, 0.22)',
                '2xl': '0 0 0 1px rgba(15, 17, 21, 0.10), 0 16px 40px -16px rgba(15, 17, 21, 0.26)',
                soft: '0 0 0 1px rgba(15, 17, 21, 0.07)',
                card: '0 0 0 1px rgba(15, 17, 21, 0.08)',
                'card-md': '0 0 0 1px rgba(15, 17, 21, 0.11)',
                'card-lg': '0 0 0 1px rgba(15, 17, 21, 0.18)',
                'brand-glow': '0 0 0 1px rgba(15, 17, 21, 0.22)',
                none: 'none',
            },
            keyframes: {
                'accordion-down': {
                    from: { height: '0' },
                    to: { height: 'var(--reka-accordion-content-height)' },
                },
                'accordion-up': {
                    from: { height: 'var(--reka-accordion-content-height)' },
                    to: { height: '0' },
                },
                // Самбарын мөр шинэчлэгдэх үеийн богино анивчилт.
                flip: {
                    from: { opacity: '0', transform: 'translateY(-4px)' },
                    to: { opacity: '1', transform: 'translateY(0)' },
                },
                // Мэдэгдлийн гүйдэг мөр — агуулгыг хоёр давтаж, хагасаар шилжүүлнэ.
                marquee: {
                    from: { transform: 'translateX(0)' },
                    to: { transform: 'translateX(-50%)' },
                },
                'fade-up': {
                    from: { opacity: '0', transform: 'translateY(6px)' },
                    to: { opacity: '1', transform: 'translateY(0)' },
                },
            },
            animation: {
                'accordion-down': 'accordion-down 0.2s ease-out',
                'accordion-up': 'accordion-up 0.2s ease-out',
                flip: 'flip 0.28s ease-out both',
                'fade-up': 'fade-up 0.35s ease-out both',
                marquee: 'marquee 60s linear infinite',
                // Хуучин float хэрэглээг чимээгүй болгоно (хөвөх хөдөлгөөнгүй).
                float: 'none',
                'float-slow': 'none',
            },
        },
    },

    plugins: [forms, animate],
};
