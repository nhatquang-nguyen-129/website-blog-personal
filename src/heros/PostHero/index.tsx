import { formatDateTime } from 'src/utilities/formatDateTime'
import React from 'react'

import type { Post } from '@/payload-types'

import { Media } from '@/components/Media'
import { formatAuthors } from '@/utilities/formatAuthors'

export const PostHero: React.FC<{
  post: Post
}> = ({ post }) => {
  const { categories, heroImage, populatedAuthors, publishedAt, title } = post

  const hasAuthors =
    populatedAuthors && populatedAuthors.length > 0 && formatAuthors(populatedAuthors) !== ''

  return (
    <div className="pb-8">
      <div className="container">
        <div className="mx-auto max-w-3xl">
          {categories && categories.length > 0 && (
            <div className="mb-4 flex flex-wrap gap-x-2 text-sm font-semibold tracking-wide text-primary uppercase">
              {categories.map((category, index) => {
                if (typeof category === 'object' && category !== null) {
                  const { title: categoryTitle } = category

                  const titleToUse = categoryTitle || 'Untitled category'

                  const isLast = index === categories.length - 1

                  return (
                    <React.Fragment key={index}>
                      {titleToUse}
                      {!isLast && <React.Fragment>&middot;</React.Fragment>}
                    </React.Fragment>
                  )
                }
                return null
              })}
            </div>
          )}

          <h1 className="mb-6 font-serif text-4xl leading-tight font-semibold text-balance md:text-5xl">
            {title}
          </h1>

          <div className="flex flex-wrap items-center gap-3 border-b border-border pb-8 text-sm text-muted-foreground">
            {hasAuthors && <span className="text-foreground">{formatAuthors(populatedAuthors)}</span>}
            {hasAuthors && publishedAt && <span aria-hidden>&middot;</span>}
            {publishedAt && <time dateTime={publishedAt}>{formatDateTime(publishedAt)}</time>}
          </div>
        </div>
      </div>

      {heroImage && typeof heroImage !== 'string' && (
        <div className="container mt-10">
          <div className="relative mx-auto aspect-16/9 max-w-4xl overflow-hidden rounded-lg bg-muted">
            <Media fill imgClassName="object-cover" priority resource={heroImage} />
          </div>
        </div>
      )}
    </div>
  )
}
