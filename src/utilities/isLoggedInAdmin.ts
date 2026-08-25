import { cookies } from 'next/headers'

import { getClientSideURL } from './getURL'

/**
 * Server-only check for whether the current request has a valid admin session.
 * Used to let logged-in admins bypass maintenance mode while everyone else sees it.
 */
export const isLoggedInAdmin = async (): Promise<boolean> => {
  const cookieStore = await cookies()
  const token = cookieStore.get('payload-token')?.value

  if (!token) return false

  const meRes = await fetch(`${getClientSideURL()}/api/users/me`, {
    cache: 'no-store',
    headers: {
      Authorization: `JWT ${token}`,
    },
  })

  if (!meRes.ok) return false

  const { user } = await meRes.json()

  return Boolean(user)
}
