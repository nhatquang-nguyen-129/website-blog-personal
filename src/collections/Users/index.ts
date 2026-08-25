import type { CollectionConfig } from 'payload'

import { authenticated } from '../../access/authenticated'

export const Users: CollectionConfig = {
  slug: 'users',
  access: {
    admin: authenticated,
    create: authenticated,
    delete: authenticated,
    read: authenticated,
    update: authenticated,
  },
  admin: {
    defaultColumns: ['name', 'email'],
    useAsTitle: 'name',
  },
  auth: true,
  fields: [
    {
      name: 'name',
      type: 'text',
      admin: {
        description: 'Internal name, used for account management only — not shown to readers.',
      },
    },
    {
      name: 'authorNames',
      type: 'join',
      admin: {
        description:
          'Pen names linked to this account. Add or edit them in the Authors collection.',
      },
      collection: 'authors',
      label: 'Author Names',
      on: 'user',
    },
  ],
  timestamps: true,
}
