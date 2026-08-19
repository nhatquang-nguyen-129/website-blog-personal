'use client'
import { cn } from '@/utilities/ui'
import useClickableCard from '@/utilities/useClickableCard'
import Link from 'next/link'
import React, { Fragment } from 'react'

import type { Post } from '@/payload-types'

import { Media } from '@/components/Media'
import { formatAuthors } from '@/utilities/formatAuthors'
import { formatDateTime } from '@/utilities/formatDateTime'

export type CardPostData = Pick<
  Post,
  'slug' | 'categories' | 'meta' | 'title' | 'publishedAt' | 'populatedAuthors'
>

export const Card: React.FC<{
  alignItems?: 'center'
  className?: string
  doc?: CardPostData
  relationTo?: 'posts'
  showCategories?: boolean
  title?: string
}> = (props) => {
  const { card, link } = useClickableCard({})
  const { className, doc, relationTo, showCategories, title: titleFromProps } = props

  const { slug, categories, meta, title, publishedAt, populatedAuthors } = doc || {}
  const { description, image: metaImage } = meta || {}

  const hasCategories = categories && Array.isArray(categories) && categories.length > 0
  const titleToUse = titleFromProps || title
  const sanitizedDescription = description?.replace(/\s/g, ' ') // replace non-breaking space with white space
  const href = `/${relationTo}/${slug}`

  const authorNames =
    populatedAuthors && populatedAuthors.length > 0 ? formatAuthors(populatedAuthors) : ''

  return (
    <article
      className={cn(
        'group flex items-start justify-between gap-6 border-b border-border py-8 first:pt-0 hover:cursor-pointer',
        className,
      )}
      ref={card.ref}
    >
      <div className="min-w-0 flex-1">
        {showCategories && hasCategories && (
          <div className="mb-2 flex flex-wrap gap-x-2 text-xs font-semibold tracking-wide text-primary uppercase">
            {categories?.map((category, index) => {
              if (typeof category === 'object') {
                const { title: titleFromCategory } = category

                const categoryTitle = titleFromCategory || 'Untitled category'

                const isLast = index === categories.length - 1

                return (
                  <Fragment key={index}>
                    {categoryTitle}
                    {!isLast && <Fragment>&middot;</Fragment>}
                  </Fragment>
                )
              }

              return null
            })}
          </div>
        )}
        {titleToUse && (
          <h3 className="mb-2 font-serif text-xl leading-snug font-semibold text-balance md:text-2xl">
            <Link className="group-hover:underline" href={href} ref={link.ref}>
              {titleToUse}
            </Link>
          </h3>
        )}
        {description && (
          <p className="mb-3 line-clamp-2 text-muted-foreground">{sanitizedDescription}</p>
        )}
        {(authorNames || publishedAt) && (
          <div className="flex items-center gap-2 text-sm text-muted-foreground">
            {authorNames && <span>{authorNames}</span>}
            {authorNames && publishedAt && <span aria-hidden>&middot;</span>}
            {publishedAt && <time dateTime={publishedAt}>{formatDateTime(publishedAt)}</time>}
          </div>
        )}
      </div>
      {metaImage && typeof metaImage !== 'string' && (
        <Link
          className="relative hidden aspect-square w-28 shrink-0 overflow-hidden rounded-md bg-muted sm:block md:w-36"
          href={href}
          tabIndex={-1}
        >
          <Media
            className="h-full w-full"
            imgClassName="h-full w-full object-cover"
            resource={metaImage}
            size="15vw"
          />
        </Link>
      )}
    </article>
  )
}
