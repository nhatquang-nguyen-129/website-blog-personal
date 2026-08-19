/** @type {import('tailwindcss').Config} */
const config = {
  theme: {
    extend: {
      typography: {
        DEFAULT: {
          css: [
            {
              '--tw-prose-body': 'var(--foreground)',
              '--tw-prose-headings': 'var(--foreground)',
              '--tw-prose-bold': 'var(--foreground)',
              '--tw-prose-links': 'var(--primary)',
              '--tw-prose-bullets': 'var(--primary)',
              '--tw-prose-quotes': 'var(--foreground)',
              '--tw-prose-quote-borders': 'var(--primary)',
              fontFamily: 'var(--font-serif)',
              fontSize: '1.125rem',
              lineHeight: '1.75',
              h1: {
                fontWeight: 'normal',
                marginBottom: '0.25em',
              },
              a: {
                textDecoration: 'underline',
                textUnderlineOffset: '3px',
              },
            },
          ],
        },
        base: {
          css: [
            {
              h1: {
                fontFamily: 'var(--font-serif)',
                fontSize: '2.5rem',
              },
              h2: {
                fontFamily: 'var(--font-serif)',
                fontSize: '1.25rem',
                fontWeight: 600,
              },
              h3: {
                fontFamily: 'var(--font-serif)',
              },
            },
          ],
        },
        md: {
          css: [
            {
              h1: {
                fontSize: '3.5rem',
              },
              h2: {
                fontSize: '1.5rem',
              },
            },
          ],
        },
      },
    },
  },
}

export default config
