import { useNavigate, useParams } from 'react-router-dom'
import {
  Avatar,
  Box,
  Button,
  Card,
  CardContent,
  CircularProgress,
  Divider,
  List,
  ListItem,
  ListItemText,
  Stack,
  Typography,
} from '@mui/material'
import ArrowBackIcon from '@mui/icons-material/ArrowBack'
import { useQuery } from '@tanstack/react-query'
import { useTranslation } from 'react-i18next'
import { api } from '../api/client'
import StatusChip from '../components/StatusChip'
import type { Employee } from '../types'

interface Movement {
  id: number
  type: string
  description: string | null
  created_at: string
  user?: { id: number; name: string }
}

function Field({ label, value }: { label: string; value: React.ReactNode }) {
  return (
    <Box sx={{ display: 'flex', justifyContent: 'space-between', py: 0.75, gap: 2 }}>
      <Typography variant="body2" color="text.secondary">
        {label}
      </Typography>
      <Typography variant="body2" sx={{ fontWeight: 600, textAlign: 'right' }}>
        {value ?? '—'}
      </Typography>
    </Box>
  )
}

export default function EmployeeDetailPage() {
  const { t } = useTranslation()
  const { id } = useParams()
  const navigate = useNavigate()

  const { data: emp, isLoading } = useQuery({
    queryKey: ['employee', id],
    queryFn: async () => (await api.get<{ data: Employee }>(`/employees/${id}`)).data.data,
  })

  const { data: movements } = useQuery({
    queryKey: ['employee-movements', id],
    queryFn: async () => (await api.get<{ data: Movement[] }>(`/employees/${id}/movements`)).data.data,
  })

  if (isLoading || !emp) {
    return (
      <Box sx={{ display: 'flex', justifyContent: 'center', mt: 8 }}>
        <CircularProgress />
      </Box>
    )
  }

  return (
    <Box>
      <Button startIcon={<ArrowBackIcon />} onClick={() => navigate('/employees')} sx={{ mb: 2 }}>
        {t('employees.back')}
      </Button>

      <Card sx={{ mb: 3 }}>
        <CardContent sx={{ display: 'flex', alignItems: 'center', gap: 3, flexWrap: 'wrap' }}>
          <Avatar sx={{ width: 72, height: 72, bgcolor: 'primary.main', fontSize: 28 }}>
            {emp.prenom.charAt(0)}
            {emp.nom.charAt(0)}
          </Avatar>
          <Box>
            <Typography variant="h5">{emp.full_name}</Typography>
            <Typography variant="body2" color="text.secondary" sx={{ fontFamily: 'monospace' }}>
              {emp.matricule}
            </Typography>
            <Box sx={{ mt: 1 }}>
              <StatusChip status={emp.status} />
            </Box>
          </Box>
        </CardContent>
      </Card>

      <Box sx={{ display: 'grid', gap: 3, gridTemplateColumns: { xs: '1fr', md: '1fr 1fr' } }}>
        <Card>
          <CardContent>
            <Typography variant="h6" sx={{ mb: 1 }}>
              {t('employees.identity')}
            </Typography>
            <Field label="Sexe" value={emp.sexe} />
            <Field label="Date de naissance" value={emp.date_naissance} />
            <Field label="Lieu de naissance" value={emp.lieu_naissance} />
            <Field label="Nationalité" value={emp.nationalite} />
            <Field label="État civil" value={emp.etat_civil} />
          </CardContent>
        </Card>

        <Card>
          <CardContent>
            <Typography variant="h6" sx={{ mb: 1 }}>
              {t('employees.contact')}
            </Typography>
            <Field label="E-mail" value={emp.email} />
            <Field label="Téléphone" value={emp.telephone} />
            <Field label="Adresse" value={emp.adresse} />
          </CardContent>
        </Card>

        <Card>
          <CardContent>
            <Typography variant="h6" sx={{ mb: 1 }}>
              {t('employees.contract')}
            </Typography>
            <Field label={t('employees.department')} value={emp.department?.name} />
            <Field label="Service" value={emp.service?.name} />
            <Field label={t('employees.job')} value={emp.job_title?.name} />
            <Field label="Type de contrat" value={emp.contract_type} />
            <Field label="Date d'embauche" value={emp.hire_date} />
            <Field label="Fin de contrat" value={emp.contract_end_date} />
            <Field label={t('employees.salary')} value={new Intl.NumberFormat('fr-FR').format(emp.base_salary)} />
          </CardContent>
        </Card>

        <Card>
          <CardContent>
            <Typography variant="h6" sx={{ mb: 1 }}>
              {t('employees.history')}
            </Typography>
            {movements && movements.length > 0 ? (
              <List dense>
                {movements.map((m) => (
                  <Box key={m.id}>
                    <ListItem disableGutters>
                      <ListItemText
                        primary={m.description ?? m.type}
                        secondary={`${new Date(m.created_at).toLocaleString('fr-FR')}${m.user ? ' · ' + m.user.name : ''}`}
                      />
                    </ListItem>
                    <Divider component="li" />
                  </Box>
                ))}
              </List>
            ) : (
              <Typography variant="body2" color="text.disabled">
                {t('dashboard.none')}
              </Typography>
            )}
          </CardContent>
        </Card>
      </Box>

      <Stack sx={{ mt: 4 }} />
    </Box>
  )
}
