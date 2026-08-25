import React from 'react'

import type { TableOfContentsBlock as TableOfContentsBlockProps } from '@/payload-types'

import type { HeadingItem } from '@/utilities/extractHeadings'
import { cn } from '@/utilities/ui'

type Props = TableOfContentsBlockProps & {
  headings: HeadingItem[]
}

export const TableOfContentsBlockComponent: React.FC<Props> = ({ title, style, headings }) => {
  if (!headings || headings.length < 2) return null

  const minLevel = Math.min(...headings.map((heading) => heading.level))

  return (
    <nav
      aria-label="Table of contents"
      className={cn(
        'not-prose my-8 text-sm',
        style === 'plain' ? '' : 'rounded-lg border border-border bg-secondary/30 p-5',
      )}
    >
      {title && <p className="mb-3 font-semibold text-foreground">{title}</p>}
      <ul className="space-y-2">
        {headings.map((heading) => (
          <li key={heading.id} style={{ marginLeft: (heading.level - minLevel) * 16 }}>
            <a
              className="text-muted-foreground transition-colors hover:text-primary"
              href={`#${heading.id}`}
            >
              {heading.text}
            </a>
          </li>
        ))}
      </ul>
    </nav>
  )
}
