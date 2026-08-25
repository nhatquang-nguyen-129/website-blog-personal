import config from '@payload-config'
import { getPayload } from 'payload'
import React from 'react'

const AdminLogo: React.FC = async () => {
  const payload = await getPayload({ config })
  const header = await payload.findGlobal({ slug: 'header', depth: 1 })
  const brand = header?.brand

  if (brand?.type === 'image' && brand.image && typeof brand.image === 'object' && brand.image.url) {
    return (
      // eslint-disable-next-line @next/next/no-img-element
      <img
        alt="Logo"
        src={brand.image.url}
        style={{ borderRadius: '9999px', height: 60, objectFit: 'cover', width: 60 }}
      />
    )
  }

  const text = brand?.type === 'text' && brand.text ? brand.text : 'Personal Blog'

  return <span style={{ fontSize: 28, fontWeight: 600 }}>{text}</span>
}

export default AdminLogo
