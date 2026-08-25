import type { GlobalConfig } from 'payload'

import { revalidateMaintenance } from './hooks/revalidateMaintenance'

export const Maintenance: GlobalConfig = {
  slug: 'maintenance',
  access: {
    read: () => true,
  },
  admin: {
    description:
      'Toggle this on to show a maintenance page to everyone except logged-in admins, while you update the site.',
  },
  fields: [
    {
      name: 'enabled',
      type: 'checkbox',
      defaultValue: false,
      label: 'Maintenance mode enabled',
    },
    {
      name: 'title',
      type: 'text',
      admin: {
        condition: (_, siblingData) => Boolean(siblingData?.enabled),
      },
      defaultValue: 'Site under maintenance',
    },
    {
      name: 'message',
      type: 'textarea',
      admin: {
        condition: (_, siblingData) => Boolean(siblingData?.enabled),
      },
      defaultValue: "We're upgrading things to bring you a better experience. Back soon.",
    },
  ],
  hooks: {
    afterChange: [revalidateMaintenance],
  },
}
