import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import {
  Box,
  Button,
  Card,
  CircularProgress,
  InputAdornment,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TablePagination,
  TableRow,
  TextField,
  Typography,
} from '@mui/material'
import SearchIcon from '@mui/icons-material/Search'
import VisibilityIcon from '@mui/icons-material/Visibility'
import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { useTranslation } from 'react-i18next'
import { api } from '../api/client'
import StatusChip from '../components/StatusChip'
import type { Employee, Paginated } from '../types'

function formatMoney(value: number) {
  return new Intl.NumberFormat('fr-FR').format(value)
}

export default function EmployeesPage() {
  const { t } = useTranslation()
  const navigate = useNavigate()
  const [search, setSearch] = useState('')
  const [page, setPage] = useState(0)
  const [perPage, setPerPage] = useState(10)

  const { data, isFetching } = useQuery({
    queryKey: ['employees', search, page, perPage],
    queryFn: async () =>
      (
        await api.get<Paginated<Employee>>('/employees', {
          params: { search, page: page + 1, per_page: perPage },
        })
      ).data,
    placeholderData: keepPreviousData,
  })

  return (
    <Box>
      <Typography variant="h4" sx={{ mb: 3 }}>
        {t('employees.title')}
      </Typography>

      <Card sx={{ mb: 2, p: 2 }}>
        <TextField
          value={search}
          onChange={(e) => {
            setSearch(e.target.value)
            setPage(0)
          }}
          placeholder={t('employees.search')}
          fullWidth
          size="small"
          InputProps={{
            startAdornment: (
              <InputAdornment position="start">
                <SearchIcon fontSize="small" />
              </InputAdornment>
            ),
          }}
        />
      </Card>

      <Card>
        <TableContainer>
          <Table>
            <TableHead>
              <TableRow sx={{ '& th': { fontWeight: 700, bgcolor: '#f8f9fc' } }}>
                <TableCell>{t('employees.matricule')}</TableCell>
                <TableCell>{t('employees.name')}</TableCell>
                <TableCell>{t('employees.department')}</TableCell>
                <TableCell>{t('employees.job')}</TableCell>
                <TableCell>{t('employees.status')}</TableCell>
                <TableCell align="right">{t('employees.salary')}</TableCell>
                <TableCell align="right">{t('employees.actions')}</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {isFetching && !data ? (
                <TableRow>
                  <TableCell colSpan={7} align="center" sx={{ py: 6 }}>
                    <CircularProgress size={28} />
                  </TableCell>
                </TableRow>
              ) : data && data.data.length > 0 ? (
                data.data.map((emp) => (
                  <TableRow key={emp.id} hover>
                    <TableCell sx={{ fontFamily: 'monospace' }}>{emp.matricule}</TableCell>
                    <TableCell>{emp.full_name}</TableCell>
                    <TableCell>{emp.department?.name ?? '—'}</TableCell>
                    <TableCell>{emp.job_title?.name ?? '—'}</TableCell>
                    <TableCell>
                      <StatusChip status={emp.status} />
                    </TableCell>
                    <TableCell align="right">{formatMoney(emp.base_salary)}</TableCell>
                    <TableCell align="right">
                      <Button
                        size="small"
                        startIcon={<VisibilityIcon />}
                        onClick={() => navigate(`/employees/${emp.id}`)}
                      >
                        {t('employees.view')}
                      </Button>
                    </TableCell>
                  </TableRow>
                ))
              ) : (
                <TableRow>
                  <TableCell colSpan={7} align="center" sx={{ py: 6, color: 'text.disabled' }}>
                    {t('employees.none')}
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>
        </TableContainer>
        {data && (
          <TablePagination
            component="div"
            count={data.meta.total}
            page={page}
            onPageChange={(_, p) => setPage(p)}
            rowsPerPage={perPage}
            onRowsPerPageChange={(e) => {
              setPerPage(parseInt(e.target.value, 10))
              setPage(0)
            }}
            rowsPerPageOptions={[10, 15, 25, 50]}
          />
        )}
      </Card>
    </Box>
  )
}
