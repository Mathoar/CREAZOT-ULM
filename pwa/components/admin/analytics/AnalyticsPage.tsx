import { useEffect, useMemo, useState } from 'react';
import { useSessionContext } from '../../admin/SessionContextProvider';
import { Title } from 'react-admin';
import { Box, Card, CardContent, Tab, Tabs, ToggleButton, ToggleButtonGroup, Typography, Grid, TextField, Select, MenuItem, Skeleton } from '@mui/material';
import { CommercialTab } from './CommercialTab';
import { OperationnelTab } from './OperationnelTab';
import { TechniqueTab } from './TechniqueTab';
import { FiscalTab } from './FiscalTab';

const granularities = [
  { id: 'day', label: 'Jour' },
  { id: 'week', label: 'Semaine' },
  { id: 'month', label: 'Mois' },
  { id: 'quarter', label: 'Trimestre' },
  { id: 'semester', label: 'Semestre' },
  { id: 'year', label: 'Année' },
];

const presets = [
  { id: 'this_week', label: 'Cette semaine' },
  { id: 'this_month', label: 'Ce mois' },
  { id: 'this_quarter', label: 'Ce trimestre' },
  { id: 'this_year', label: 'Cette année' },
  { id: 'last_12', label: '12 derniers mois' },
  { id: 'custom', label: 'Personnalisé' },
];

const getPresetDates = (preset: string): { from: string; to: string } => {
  const now = new Date();
  const y = now.getFullYear();
  const m = now.getMonth();
  const to = now.toISOString().slice(0, 10);

  switch (preset) {
    case 'this_week': {
      const day = now.getDay() || 7;
      const monday = new Date(now);
      monday.setDate(now.getDate() - day + 1);
      return { from: monday.toISOString().slice(0, 10), to };
    }
    case 'this_month':
      return { from: `${y}-${String(m + 1).padStart(2, '0')}-01`, to };
    case 'this_quarter': {
      const qStart = new Date(y, Math.floor(m / 3) * 3, 1);
      return { from: qStart.toISOString().slice(0, 10), to };
    }
    case 'this_year':
      return { from: `${y}-01-01`, to };
    case 'last_12': {
      const past = new Date(y - 1, m, now.getDate() + 1);
      return { from: past.toISOString().slice(0, 10), to };
    }
    default:
      return { from: `${y}-01-01`, to };
  }
};

const bestGranularity = (preset: string): string => {
  switch (preset) {
    case 'this_week': return 'day';
    case 'this_month': return 'day';
    case 'this_quarter': return 'week';
    case 'this_year': return 'month';
    case 'last_12': return 'month';
    default: return 'month';
  }
};

export const AnalyticsPage = () => {
  const { session } = useSessionContext();
  const [tab, setTab] = useState(0);
  const [preset, setPreset] = useState('this_year');
  const [granularity, setGranularity] = useState('month');
  const [customFrom, setCustomFrom] = useState('');
  const [customTo, setCustomTo] = useState('');
  const [data, setData] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  const { from, to } = useMemo(() => {
    if (preset === 'custom' && customFrom && customTo) return { from: customFrom, to: customTo };
    return getPresetDates(preset);
  }, [preset, customFrom, customTo]);

  useEffect(() => {
    if (preset !== 'custom') setGranularity(bestGranularity(preset));
  }, [preset]);

  useEffect(() => {
    if (!from || !to || !session?.accessToken) return;
    setData(null);
    setLoading(true);

    const headers: Record<string, string> = { Authorization: `Bearer ${session.accessToken}` };
    try {
      const raw = sessionStorage.getItem('client');
      if (raw) {
        const parsed = JSON.parse(raw);
        if (parsed?.id) headers['X-Client-Id'] = String(parsed.id);
      }
    } catch (e) {}

    const endpoint = tab === 0 ? 'commercial' : tab === 1 ? 'operational' : tab === 2 ? 'technical' : 'fiscal';

    fetch(`/admin/stats/${endpoint}?from=${from}&to=${to}&granularity=${granularity}`, { headers })
      .then(r => r.json())
      .then(d => { setData(d); setLoading(false); })
      .catch(() => setLoading(false));
  }, [from, to, granularity, tab, session?.accessToken]);

  return (
    <Box sx={{ p: { xs: 1, md: 2 } }}>
      <Title title="Statistiques" />

      {/* Filters bar */}
      <Card sx={{ mb: 2 }}>
        <CardContent sx={{ display: 'flex', flexWrap: 'wrap', gap: 2, alignItems: 'center', py: '12px !important' }}>
          <Select size="small" value={preset} onChange={e => setPreset(e.target.value)} sx={{ minWidth: 160 }}>
            {presets.map(p => <MenuItem key={p.id} value={p.id}>{p.label}</MenuItem>)}
          </Select>

          {preset === 'custom' && (
            <>
              <TextField size="small" type="date" label="Du" value={customFrom} onChange={e => setCustomFrom(e.target.value)} InputLabelProps={{ shrink: true }} />
              <TextField size="small" type="date" label="Au" value={customTo} onChange={e => setCustomTo(e.target.value)} InputLabelProps={{ shrink: true }} />
            </>
          )}

          <ToggleButtonGroup size="small" exclusive value={granularity} onChange={(_, v) => v && setGranularity(v)}>
            {granularities.map(g => <ToggleButton key={g.id} value={g.id}>{g.label}</ToggleButton>)}
          </ToggleButtonGroup>
        </CardContent>
      </Card>

      {/* Tabs */}
      <Card>
        <Tabs value={tab} onChange={(_, v) => setTab(v)} variant="fullWidth"
          sx={{ borderBottom: 1, borderColor: 'divider' }}>
          <Tab label="Commercial" />
          <Tab label="Opérationnel" />
          <Tab label="Technique" />
          <Tab label="Fiscal / TVA" />
        </Tabs>
        <CardContent>
          {tab === 0 && <CommercialTab data={data} loading={loading} />}
          {tab === 1 && <OperationnelTab data={data} loading={loading} />}
          {tab === 2 && <TechniqueTab data={data} loading={loading} />}
          {tab === 3 && <FiscalTab data={data} loading={loading} />}
        </CardContent>
      </Card>
    </Box>
  );
};
