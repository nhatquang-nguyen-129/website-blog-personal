import type { GlobalConfig } from 'payload'

import { link } from '@/fields/link'
import { revalidateHeader } from './hooks/revalidateHeader'

export const Header: GlobalConfig = {
  slug: 'header',
  access: {
    read: () => true,
  },
  fields: [
    {
      name: 'brand',
      type: 'group',
      fields: [
        {
          name: 'type',
          type: 'radio',
          admin: {
            layout: 'horizontal',
          },
          defaultValue: 'text',
          options: [
            {
              label: 'Text',
              value: 'text',
            },
            {
              label: 'Image',
              value: 'image',
            },
          ],
        },
        {
          name: 'text',
          type: 'text',
          admin: {
            condition: (_, siblingData) => siblingData?.type === 'text',
          },
          defaultValue: 'Personal Blog',
        },
        {
          name: 'image',
          type: 'upload',
          admin: {
            condition: (_, siblingData) => siblingData?.type === 'image',
            description: 'Rendered as a circular avatar, similar to a Facebook profile picture.',
          },
          relationTo: 'media',
        },
      ],
    },
    {
      name: 'navItems',
      type: 'array',
      fields: [
        link({
          appearances: false,
        }),
      ],
      maxRows: 6,
      admin: {
        initCollapsed: true,
        components: {
          RowLabel: '@/Header/RowLabel#RowLabel',
        },
      },
    },
  ],
  hooks: {
    afterChange: [revalidateHeader],
  },
}
