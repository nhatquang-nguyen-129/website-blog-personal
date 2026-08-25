export type HeadingItem = {
  id: string
  level: number
  text: string
}

type LexicalNode = {
  children?: LexicalNode[]
  tag?: string
  text?: string
  type?: string
}

const DIACRITICS_REGEX = /[̀-ͯ]/g

const slugify = (text: string) =>
  text
    .normalize('NFD')
    .replace(DIACRITICS_REGEX, '') // strip combining diacritics (áàảãạ -> a, etc.)
    .replace(/đ/g, 'd')
    .replace(/Đ/g, 'D')
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')

const getNodeText = (node: LexicalNode): string => {
  if (typeof node.text === 'string') return node.text
  if (Array.isArray(node.children)) return node.children.map(getNodeText).join('')
  return ''
}

/**
 * Walks a Lexical richText JSON tree and pulls out heading nodes (h1-h4),
 * assigning each a stable, deduplicated slug id.
 */
export const extractHeadings = (
  data?: { root?: { children?: LexicalNode[] } } | null,
): HeadingItem[] => {
  const children = data?.root?.children

  if (!Array.isArray(children)) return []

  const headings: HeadingItem[] = []
  const seen = new Map<string, number>()

  for (const node of children) {
    if (node.type !== 'heading' || typeof node.tag !== 'string') continue

    const level = Number(node.tag.replace('h', ''))
    const text = getNodeText(node).trim()

    if (!text || Number.isNaN(level)) continue

    const base = slugify(text) || 'heading'
    const count = seen.get(base) ?? 0
    seen.set(base, count + 1)

    headings.push({
      id: count > 0 ? `${base}-${count}` : base,
      level,
      text,
    })
  }

  return headings
}
