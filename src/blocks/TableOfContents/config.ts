import type { Block } from 'payload'

export const TableOfContents: Block = {
  slug: 'tableOfContents',
  fields: [
    {
      name: 'title',
      type: 'text',
      admin: {
        description: 'Shown as the heading above the list of links. Leave empty to hide it.',
      },
      defaultValue: 'On this page',
    },
    {
      name: 'style',
      type: 'select',
      admin: {
        description: 'How the table of contents is styled on the page.',
      },
      defaultValue: 'box',
      options: [
        { label: 'Boxed', value: 'box' },
        { label: 'Plain', value: 'plain' },
      ],
      required: true,
    },
  ],
  interfaceName: 'TableOfContentsBlock',
  labels: {
    plural: 'Tables of Contents',
    singular: 'Table of Contents',
  },
}
