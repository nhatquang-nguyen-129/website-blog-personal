import { cn } from '@/utilities/ui'
import React from 'react'

import { Card, CardPostData } from '@/components/Card'

export type Props = {
  posts: CardPostData[]
}

export const CollectionArchive: React.FC<Props> = (props) => {
  const { posts } = props

  return (
    <div className={cn('container')}>
      <div className="mx-auto max-w-3xl">
        {posts?.map((result, index) => {
          if (typeof result === 'object' && result !== null) {
            return <Card key={index} doc={result} relationTo="posts" showCategories />
          }

          return null
        })}
      </div>
    </div>
  )
}
