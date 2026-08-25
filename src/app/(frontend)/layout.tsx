import type { Metadata } from 'next'

import { cn } from '@/utilities/ui'
import { GeistMono } from 'geist/font/mono'
import { GeistSans } from 'geist/font/sans'
import { Source_Serif_4 } from 'next/font/google'
import React from 'react'

import { Footer } from '@/Footer/Component'
import { Header } from '@/Header/Component'
import { MaintenancePage } from '@/Maintenance/Component'
import { Providers } from '@/providers'
import { InitTheme } from '@/providers/Theme/InitTheme'
import { getCachedGlobal } from '@/utilities/getGlobals'
import { isLoggedInAdmin } from '@/utilities/isLoggedInAdmin'
import { mergeOpenGraph } from '@/utilities/mergeOpenGraph'

import './globals.css'
import { getServerSideURL } from '@/utilities/getURL'

const sourceSerif = Source_Serif_4({
  subsets: ['latin', 'vietnamese'],
  variable: '--font-source-serif',
})

export default async function RootLayout({ children }: { children: React.ReactNode }) {
  const maintenance = await getCachedGlobal('maintenance', 0)()
  const showMaintenance = Boolean(maintenance?.enabled) && !(await isLoggedInAdmin())

  let body: React.ReactNode

  if (showMaintenance) {
    const header = await getCachedGlobal('header', 1)()
    body = <MaintenancePage brand={header?.brand} message={maintenance?.message} title={maintenance?.title} />
  } else {
    body = (
      <Providers>
        <Header />
        {children}
        <Footer />
      </Providers>
    )
  }

  return (
    <html
      className={cn(GeistSans.variable, GeistMono.variable, sourceSerif.variable)}
      lang="en"
      suppressHydrationWarning
    >
      <head>
        <InitTheme />
        <link href="/favicon.ico" rel="icon" sizes="32x32" />
        <link href="/favicon.svg" rel="icon" type="image/svg+xml" />
      </head>
      <body>{body}</body>
    </html>
  )
}

export const metadata: Metadata = {
  metadataBase: new URL(getServerSideURL()),
  openGraph: mergeOpenGraph(),
  twitter: {
    card: 'summary_large_image',
  },
}
