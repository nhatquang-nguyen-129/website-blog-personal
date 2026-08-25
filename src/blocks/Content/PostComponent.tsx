import { cn } from '@/utilities/ui'
import React from 'react'
import RichText from '@/components/RichText'

import type { ContentBlock as ContentBlockProps } from '@/payload-types'

import { CMSLink } from '../../components/Link'

const colsSpanClasses = {
  full: '12',
  half: '6',
  oneThird: '4',
  twoThirds: '8',
}

/**
 * Same columns/size fields as ContentBlock (Content/Component.tsx), rendered without the
 * page-level `container`/`my-16` wrapper so it sits flush inside a Post's narrower prose column.
 */
export const PostContentColumns: React.FC<ContentBlockProps> = ({ columns }) => {
  if (!columns || columns.length === 0) return null

  return (
    <div className="not-prose my-8 grid grid-cols-4 gap-x-8 gap-y-8 lg:grid-cols-12">
      {columns.map((col, index) => {
        const { enableLink, link, richText, size } = col

        return (
          <div
            className={cn(`col-span-4 lg:col-span-${colsSpanClasses[size!]}`, {
              'md:col-span-2': size !== 'full',
            })}
            key={index}
          >
            {richText && <RichText data={richText} enableGutter={false} />}

            {enableLink && <CMSLink {...link} />}
          </div>
        )
      })}
    </div>
  )
}
