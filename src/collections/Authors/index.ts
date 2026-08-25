import type { CollectionConfig } from 'payload'

import { authenticated } from '../../access/authenticated'

export const Authors: CollectionConfig = {
  slug: 'authors',
  access: {
    create: authenticated,
    delete: authenticated,
    read: () => true,
    update: authenticated,
  },
  admin: {
    defaultColumns: ['name', 'user'],
    description: 'Pen names shown as the byline on posts. Each one belongs to a User account.',
    useAsTitle: 'name',
  },
  fields: [
    {
      name: 'name',
      type: 'text',
      label: 'Author Name',
      required: true,
    },
    {
      name: 'user',
      type: 'relationship',
      admin: {
        description: 'Which login account this pen name belongs to.',
      },
      label: 'Account',
      relationTo: 'users',
      required: true,
    },
  ],
  timestamps: true,
}
