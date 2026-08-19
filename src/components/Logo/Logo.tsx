import { cn } from '@/utilities/ui'
import React from 'react'

import type { Header } from '@/payload-types'

import { Media } from '@/components/Media'

interface Props {
  brand?: Header['brand']
  className?: string
}

export const Logo = ({ brand, className }: Props) => {
  if (brand?.type === 'image' && brand.image && typeof brand.image === 'object') {
    return (
      <span
        className={cn(
          'relative block h-10 w-10 shrink-0 overflow-hidden rounded-full bg-muted',
          className,
        )}
      >
        <Media fill imgClassName="object-cover" resource={brand.image} />
      </span>
    )
  }

  const text = brand?.type === 'text' && brand.text ? brand.text : 'Personal Blog'

  return (
    <span
      className={cn('font-serif text-2xl font-semibold tracking-tight text-foreground', className)}
    >
      {text}
    </span>
  )
}
