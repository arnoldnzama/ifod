import { Box, Card, CardContent, CircularProgress, Divider, List, ListItem, ListItemText, Typography } from '@mui/material'
import GroupsIcon from '@mui/icons-material/Groups2'
import CheckCircleIcon from '@mui/icons-material/CheckCircle'
import BeachAccessIcon from '@mui/icons-material/BeachAccess'
import FlightIcon from '@mui/icons-material/FlightTakeoff'
import BlockIcon from '@mui/icons-material/Block'
import PaymentsIcon from '@mui/icons-material/Payments'
import CakeIcon from '@mui/icons-material/Cake'
import EventBusyIcon from '@mui/icons-material/EventBusy'
import { useQuery } from '@tanstack/react-query'
import { useTranslation } from 'react-i18next'
import {
  Bar,
  BarChart,
  CartesianGrid,
  Cell,
  Legend,
  Line,
  LineChart,
  Pie,
  PieChart,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts'
import { api } from '../api/client'
import StatCard from '../components/StatCard'
import type { DashboardData } from '../types'

const PIE_COLORS = ['#1238a8', '#1e5eff', '#16a34a', '#d97706', '#dc2626', '#7c3aed']

function formatMoney(value: number) {
  return new Intl.NumberFormat('fr-FR').format(value) + ' CDF'
}

function ChartCard({ title, children }: { title: string; children: React.ReactNode }) {
  return (
    <Card sx={{ height: '100%' }}>
      <CardContent>
        <Typography variant="h6" sx={{ mb: 2 }}>
          {title}
        </Typography>
        <Box sx={{ height: 280 }}>{children}</Box>
      </CardContent>
    </Card>
  )
}

export default function DashboardPage() {
  const { t } = useTranslation()
  const { data, isLoading } = useQuery({
    queryKey: ['dashboard'],
    queryFn: async () => (await api.get<DashboardData>('/dashboard')).data,
  })

  if (isLoading || !data) {
    return (
      <Box sx={{ display: 'flex', justifyContent: 'center', mt: 8 }}>
        <CircularProgress />
      </Box>
    )
  }

  const { kpis, alerts, charts } = data

  return (
    <Box>
      <Typography variant="h4" sx={{ mb: 0.5 }}>
        {t('dashboard.title')}
      </Typography>
      <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
        {t('app.name')}
      </Typography>

      <Box
        sx={{
          display: 'grid',
          gap: 2,
          gridTemplateColumns: { xs: '1fr 1fr', sm: 'repeat(3, 1fr)', lg: 'repeat(6, 1fr)' },
          mb: 3,
        }}
      >
        <StatCard index={0} label={t('dashboard.total')} value={kpis.total_employees} icon={<GroupsIcon />} />
        <StatCard index={1} label={t('dashboard.active')} value={kpis.active} icon={<CheckCircleIcon />} accent="#16a34a" />
        <StatCard index={2} label={t('dashboard.on_leave')} value={kpis.on_leave} icon={<BeachAccessIcon />} accent="#d97706" />
        <StatCard index={3} label={t('dashboard.on_mission')} value={kpis.on_mission} icon={<FlightIcon />} accent="#1e5eff" />
        <StatCard index={4} label={t('dashboard.suspended')} value={kpis.suspended} icon={<BlockIcon />} accent="#dc2626" />
        <StatCard index={5} label={t('dashboard.payroll')} value={formatMoney(kpis.masse_salariale)} icon={<PaymentsIcon />} accent="#7c3aed" />
      </Box>

      <Box
        sx={{
          display: 'grid',
          gap: 2,
          gridTemplateColumns: { xs: '1fr', lg: '2fr 1fr' },
          mb: 3,
        }}
      >
        <ChartCard title={t('dashboard.headcount_by_dept')}>
          <ResponsiveContainer width="100%" height="100%">
            <BarChart data={charts.headcount_by_department}>
              <CartesianGrid strokeDasharray="3 3" vertical={false} />
              <XAxis dataKey="label" fontSize={12} />
              <YAxis allowDecimals={false} fontSize={12} />
              <Tooltip />
              <Bar dataKey="value" fill="#1238a8" radius={[6, 6, 0, 0]} />
            </BarChart>
          </ResponsiveContainer>
        </ChartCard>

        <ChartCard title={t('dashboard.gender')}>
          <ResponsiveContainer width="100%" height="100%">
            <PieChart>
              <Pie data={charts.gender_distribution} dataKey="value" nameKey="label" outerRadius={90} label>
                {charts.gender_distribution.map((_, i) => (
                  <Cell key={i} fill={PIE_COLORS[i % PIE_COLORS.length]} />
                ))}
              </Pie>
              <Legend />
              <Tooltip />
            </PieChart>
          </ResponsiveContainer>
        </ChartCard>
      </Box>

      <Box
        sx={{
          display: 'grid',
          gap: 2,
          gridTemplateColumns: { xs: '1fr', lg: '2fr 1fr' },
        }}
      >
        <ChartCard title={t('dashboard.salary_evolution')}>
          <ResponsiveContainer width="100%" height="100%">
            <LineChart data={charts.salary_evolution}>
              <CartesianGrid strokeDasharray="3 3" vertical={false} />
              <XAxis dataKey="label" fontSize={12} />
              <YAxis fontSize={12} width={80} tickFormatter={(v) => new Intl.NumberFormat('fr-FR', { notation: 'compact' }).format(v)} />
              <Tooltip formatter={(v: number) => formatMoney(v)} />
              <Line type="monotone" dataKey="value" stroke="#1e5eff" strokeWidth={3} dot={{ r: 4 }} />
            </LineChart>
          </ResponsiveContainer>
        </ChartCard>

        <Card>
          <CardContent>
            <Typography variant="h6" sx={{ mb: 1 }}>
              {t('dashboard.alerts')}
            </Typography>

            <Typography variant="subtitle2" color="text.secondary" sx={{ mt: 1, display: 'flex', alignItems: 'center', gap: 0.5 }}>
              <EventBusyIcon fontSize="small" /> {t('dashboard.contracts_expiring')}
            </Typography>
            {alerts.contracts_expiring.length === 0 ? (
              <Typography variant="body2" color="text.disabled">
                {t('dashboard.none')}
              </Typography>
            ) : (
              <List dense disablePadding>
                {alerts.contracts_expiring.slice(0, 5).map((c) => (
                  <ListItem key={c.id} disableGutters>
                    <ListItemText primary={c.name} secondary={`${c.matricule} · ${c.contract_end_date}`} />
                  </ListItem>
                ))}
              </List>
            )}

            <Divider sx={{ my: 1.5 }} />

            <Typography variant="subtitle2" color="text.secondary" sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
              <CakeIcon fontSize="small" /> {t('dashboard.birthdays')}
            </Typography>
            {alerts.birthdays_this_month.length === 0 ? (
              <Typography variant="body2" color="text.disabled">
                {t('dashboard.none')}
              </Typography>
            ) : (
              <List dense disablePadding>
                {alerts.birthdays_this_month.slice(0, 5).map((b) => (
                  <ListItem key={b.id} disableGutters>
                    <ListItemText primary={b.name} secondary={`Jour ${b.day}`} />
                  </ListItem>
                ))}
              </List>
            )}
          </CardContent>
        </Card>
      </Box>
    </Box>
  )
}
