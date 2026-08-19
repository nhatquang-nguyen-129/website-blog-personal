'use client'

import { Button } from '@payloadcms/ui/elements/Button'
import { usePathname } from 'next/navigation'
import React, { useEffect, useState } from 'react'

const collectionPathPrefixes: Record<string, string> = {
  pages: '',
  posts: '/posts',
}

const collectionLabels: Record<string, string> = {
  pages: 'View Page',
  posts: 'View Post',
}

const GoToSite: React.FC = () => {
  const pathname = usePathname()
  const [url, setUrl] = useState('/')
  const [label, setLabel] = useState('Go to site')

  useEffect(() => {
    const match = pathname?.match(/^\/admin\/collections\/(pages|posts)\/([^/]+)$/)

    if (!match || match[2] === 'create') {
      setUrl('/')
      setLabel('Go to site')
      return
    }

    const [, collection, id] = match
    let cancelled = false

    fetch(`/api/${collection}/${id}?depth=0`, { credentials: 'include' })
      .then((res) => (res.ok ? res.json() : null))
      .then((doc) => {
        if (cancelled || !doc?.slug) return
        const isHomePage = collection === 'pages' && doc.slug === 'home'
        setUrl(isHomePage ? '/' : `${collectionPathPrefixes[collection]}/${doc.slug}`)
        setLabel(collectionLabels[collection])
      })
      .catch(() => {})

    return () => {
      cancelled = true
    }
  }, [pathname])

  return (
    <Button buttonStyle="secondary" el="anchor" newTab size="small" url={url}>
      {label}
    </Button>
  )
}

export default GoToSite
