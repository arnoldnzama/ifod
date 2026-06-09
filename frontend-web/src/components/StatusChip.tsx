import { Chip } from '@mui/material'
import { useTranslation } from 'react-i18next'
import type { Employee } from '../types'

const colorMap: Record<Employee['status'], 'success' | 'warning' | 'info' | 'default' | 'error'> = {
  active: 'success',
  on_leave: 'warning',
  on_mission: 'info',
  suspended: 'error',
  archived: 'default',
}

export default function StatusChip({ status }: { status: Employee['status'] }) {
  const { t } = useTranslation()
  return <Chip size="small" color={colorMap[status]} label={t(`status.${status}`)} />
}
