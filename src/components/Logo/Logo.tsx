import { cn } from '@/utilities/ui'
import React from 'react'

interface Props {
  className?: string
}

export const Logo = ({ className }: Props) => {
  return (
    <span
      className={cn('font-serif text-2xl font-semibold tracking-tight text-foreground', className)}
    >
      Blog Cá Nhân
    </span>
  )
}
