import { MediaBlock } from '@/blocks/MediaBlock/Component'
import {
  DefaultNodeTypes,
  SerializedBlockNode,
  SerializedHeadingNode,
  SerializedLinkNode,
  type DefaultTypedEditorState,
} from '@payloadcms/richtext-lexical'
import {
  JSXConvertersFunction,
  LinkJSXConverter,
  RichText as ConvertRichText,
} from '@payloadcms/richtext-lexical/react'

import { CodeBlock, CodeBlockProps } from '@/blocks/Code/Component'

import type {
  BannerBlock as BannerBlockProps,
  CallToActionBlock as CTABlockProps,
  ContentBlock as ContentBlockProps,
  MediaBlock as MediaBlockProps,
  TableOfContentsBlock as TableOfContentsBlockProps,
} from '@/payload-types'
import { BannerBlock } from '@/blocks/Banner/Component'
import { CallToActionBlock } from '@/blocks/CallToAction/Component'
import { PostContentColumns } from '@/blocks/Content/PostComponent'
import { TableOfContentsBlockComponent } from '@/blocks/TableOfContents/Component'
import { cn } from '@/utilities/ui'
import { extractHeadings } from '@/utilities/extractHeadings'

type NodeTypes =
  | DefaultNodeTypes
  | SerializedBlockNode<
      | CTABlockProps
      | MediaBlockProps
      | BannerBlockProps
      | CodeBlockProps
      | TableOfContentsBlockProps
      | ContentBlockProps
    >

const internalDocToHref = ({ linkNode }: { linkNode: SerializedLinkNode }) => {
  const { value, relationTo } = linkNode.fields.doc!
  if (typeof value !== 'object') {
    throw new Error('Expected value to be an object')
  }
  const slug = value.slug
  return relationTo === 'posts' ? `/posts/${slug}` : `/${slug}`
}

/**
 * Built fresh per RichText render (not a module-level singleton) so the heading
 * index counter below never leaks across concurrent renders of different documents.
 */
const buildJSXConverters =
  (headings: ReturnType<typeof extractHeadings>): JSXConvertersFunction<NodeTypes> =>
  ({ defaultConverters }) => {
    let headingIndex = 0

    return {
      ...defaultConverters,
      ...LinkJSXConverter({ internalDocToHref }),
      heading: ({ node, nodesToJSX }: { node: SerializedHeadingNode; nodesToJSX: any }) => {
        const children = nodesToJSX({ nodes: node.children })
        const Tag = node.tag as keyof React.JSX.IntrinsicElements
        const heading = headings[headingIndex]
        headingIndex += 1

        return <Tag id={heading?.id}>{children}</Tag>
      },
      blocks: {
        banner: ({ node }) => <BannerBlock className="col-start-2 mb-4" {...node.fields} />,
        mediaBlock: ({ node }) => (
          <MediaBlock
            className="col-start-1 col-span-3"
            imgClassName="m-0"
            {...node.fields}
            captionClassName="mx-auto max-w-[48rem]"
            enableGutter={false}
            disableInnerContainer={true}
          />
        ),
        code: ({ node }) => <CodeBlock className="col-start-2" {...node.fields} />,
        cta: ({ node }) => <CallToActionBlock {...node.fields} />,
        tableOfContents: ({ node }) => (
          <TableOfContentsBlockComponent {...node.fields} headings={headings} />
        ),
        content: ({ node }) => <PostContentColumns {...node.fields} />,
      },
    }
  }

type Props = {
  data: DefaultTypedEditorState
  enableGutter?: boolean
  enableProse?: boolean
} & React.HTMLAttributes<HTMLDivElement>

export default function RichText(props: Props) {
  const { className, enableProse = true, enableGutter = true, data, ...rest } = props
  const headings = extractHeadings(data)

  return (
    <ConvertRichText
      converters={buildJSXConverters(headings)}
      data={data}
      className={cn(
        'payload-richtext',
        {
          container: enableGutter,
          'max-w-none': !enableGutter,
          'mx-auto prose md:prose-md dark:prose-invert': enableProse,
        },
        className,
      )}
      {...rest}
    />
  )
}
