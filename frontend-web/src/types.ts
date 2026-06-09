export interface AuthUser {
  id: number
  name: string
  email: string
  locale: string
  roles: string[]
  permissions: string[]
}

export interface LoginResponse {
  access_token: string
  token_type: string
  expires_in: number
  user: AuthUser
}

export interface Employee {
  id: number
  matricule: string
  nom: string
  postnom: string | null
  prenom: string
  full_name: string
  sexe: 'M' | 'F' | 'Autre'
  date_naissance: string | null
  lieu_naissance: string | null
  nationalite: string | null
  etat_civil: string | null
  email: string | null
  telephone: string | null
  adresse: string | null
  department_id: number | null
  service_id: number | null
  job_title_id: number | null
  manager_id: number | null
  hire_date: string | null
  contract_type: string
  contract_end_date: string | null
  base_salary: number
  status: 'active' | 'suspended' | 'on_leave' | 'on_mission' | 'archived'
  department?: { id: number; name: string }
  service?: { id: number; name: string }
  job_title?: { id: number; name: string }
}

export interface Paginated<T> {
  data: T[]
  meta: { current_page: number; last_page: number; total: number; per_page: number }
}

export interface ChartPoint {
  label: string
  value: number
}

export interface DashboardData {
  kpis: {
    total_employees: number
    active: number
    suspended: number
    on_leave: number
    on_mission: number
    archived: number
    masse_salariale: number
  }
  alerts: {
    contracts_expiring: { id: number; matricule: string; name: string; contract_end_date: string }[]
    birthdays_this_month: { id: number; name: string; day: number }[]
    pending_leaves: number
    open_recruitments: number
  }
  charts: {
    headcount_by_department: ChartPoint[]
    salary_evolution: ChartPoint[]
    gender_distribution: ChartPoint[]
    contract_type_distribution: ChartPoint[]
  }
}

export interface Department {
  id: number
  name: string
  code: string
  employees_count?: number
  services_count?: number
}
