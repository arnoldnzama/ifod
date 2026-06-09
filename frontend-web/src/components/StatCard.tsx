import { Card, CardContent, Box, Typography } from '@mui/material'
import { motion } from 'framer-motion'
import type { ReactNode } from 'react'

interface Props {
  label: string
  value: string | number
  icon?: ReactNode
  accent?: string
  index?: number
}

export default function StatCard({ label, value, icon, accent = '#1238a8', index = 0 }: Props) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 16 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.35, delay: index * 0.05 }}
    >
      <Card>
        <CardContent sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
          <Box
            sx={{
              width: 48,
              height: 48,
              borderRadius: 2,
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              bgcolor: `${accent}14`,
              color: accent,
              flexShrink: 0,
            }}
          >
            {icon}
          </Box>
          <Box sx={{ minWidth: 0 }}>
            <Typography variant="body2" color="text.secondary" noWrap>
              {label}
            </Typography>
            <Typography variant="h5" sx={{ fontWeight: 800 }} noWrap>
              {value}
            </Typography>
          </Box>
        </CardContent>
      </Card>
    </motion.div>
  )
}
