import { Author } from '@/payload-types'

const nameOf = (author: Author | number | null | undefined) =>
  typeof author === 'object' && author !== null ? author.name : undefined

/**
 * Formats a post's primary author + collaborators into a prettified byline.
 * @example
 *
 * primary only -> 'Author1'
 * primary + 1 collaborator -> 'Author1 and Author2'
 * primary + 2 collaborators -> 'Author1, Author2, and Author3'
 */
export const formatAuthors = (
  primaryAuthor: Author | number | null | undefined,
  collaborators?: (Author | number | null)[] | null,
) => {
  const names = [nameOf(primaryAuthor), ...(collaborators || []).map(nameOf)].filter(
    (name): name is string => Boolean(name),
  )

  if (names.length === 0) return ''
  if (names.length === 1) return names[0]
  if (names.length === 2) return `${names[0]} and ${names[1]}`

  return `${names.slice(0, -1).join(', ')} and ${names[names.length - 1]}`
}
