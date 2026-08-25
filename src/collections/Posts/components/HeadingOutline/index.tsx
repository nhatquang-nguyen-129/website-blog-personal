'use client'

import { useFormFields } from '@payloadcms/ui'
import React from 'react'

import { extractHeadings } from '@/utilities/extractHeadings'

export const HeadingOutline: React.FC = () => {
  const contentValue = useFormFields(([fields]) => fields?.content?.value)
  const headings = extractHeadings(contentValue as Parameters<typeof extractHeadings>[0])

  return (
    <div className="field-type">
      <label className="field-label" style={{ marginBottom: 6 }}>
        Heading Outline
      </label>
      {headings.length === 0 ? (
        <p style={{ color: 'var(--theme-elevation-400)', fontSize: 13, margin: 0 }}>
          No headings yet — add H1–H4 headings in the content to see them here.
        </p>
      ) : (
        <ul style={{ fontSize: 13, listStyle: 'none', margin: 0, padding: 0 }}>
          {headings.map((heading, i) => (
            <li
              key={i}
              style={{
                borderTop: i === 0 ? undefined : '1px solid var(--theme-elevation-100)',
                paddingLeft: (heading.level - 1) * 12,
                paddingBlock: 4,
              }}
            >
              <span style={{ color: 'var(--theme-elevation-400)' }}>H{heading.level}</span>{' '}
              {heading.text}
            </li>
          ))}
        </ul>
      )}
    </div>
  )
}
