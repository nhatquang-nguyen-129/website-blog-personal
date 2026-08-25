import type { GlobalAfterChangeHook } from 'payload'

import { revalidatePath, revalidateTag } from 'next/cache'

export const revalidateMaintenance: GlobalAfterChangeHook = ({ doc, req: { payload, context } }) => {
  if (!context.disableRevalidate) {
    payload.logger.info(`Revalidating maintenance mode`)

    revalidateTag('global_maintenance', 'max')

    // Every page in the (frontend) route group renders through the same root layout,
    // which reads this global — a tag revalidation alone doesn't reliably regenerate
    // already-static/SSG pages, so force the whole layout tree to revalidate too.
    revalidatePath('/', 'layout')
  }

  return doc
}
