import { useMemo } from 'react';
import { Box, Grid, Typography, Skeleton, Card, CardContent } from '@mui/material';
import dynamic from 'next/dynamic';
import { KpiCard } from './KpiCard';
import EuroIcon from '@mui/icons-material/Euro';
import ConfirmationNumberIcon from '@mui/icons-material/ConfirmationNumber';
import CardGiftcardIcon from '@mui/icons-material/CardGiftcard';
import TrendingUpIcon from '@mui/icons-material/TrendingUp';

const Chart = dynamic(() => import('react-apexcharts'), { ssr: false });

const STATUS_LABELS: Record<string, string> = {
  'VALIDATED': 'Validé',
  'WAITING': 'En attente',
  'WHEATER_REPORT': 'Report météo',
  'PASSENGER_REPORT': 'Report client',
  'INTERN_REPORT': 'Report interne',
  'WHEATER_CANCEL': 'Annulation météo',
  'PASSENGER_CANCEL': 'Annulation client',
  'INTERN_CANCEL': 'Annulation interne',
};

const STATUS_COLORS: Record<string, string> = {
  'VALIDATED': '#2e7d32',
  'WAITING': '#ed6c02',
  'WHEATER_REPORT': '#0288d1',
  'PASSENGER_REPORT': '#9c27b0',
  'INTERN_REPORT': '#7c3aed',
  'WHEATER_CANCEL': '#d32f2f',
  'PASSENGER_CANCEL': '#e91e63',
  'INTERN_CANCEL': '#795548',
};

const MODE_LABELS: Record<string, string> = {
  'cb': 'CB', 'especes': 'Espèces', 'web': 'Site Web',
  'virement': 'Virement', 'cheque': 'Chèque',
};

const chartDefaults = {
  chart: { toolbar: { show: false }, fontFamily: 'inherit' },
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth' as const, width: 2 },
  grid: { borderColor: '#f0f0f0' },
};

const fmt = (n: number) => new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 }).format(n);

interface Props { data: any; loading: boolean; }

export const CommercialTab = ({ data, loading }: Props) => {
  if (loading || !data) {
    return (
      <Grid container spacing={2}>
        {[...Array(8)].map((_, i) => (
          <Grid item xs={12} md={6} lg={3} key={i}><Skeleton variant="rounded" height={140} /></Grid>
        ))}
      </Grid>
    );
  }

  const { revenue, revenue_source, payment_modes, circuit_types, top_circuits, reservation_statuses, ticket_moyen, prepayment_conversion, origines } = data;
  const sourceLabel = revenue_source === 'payments' ? 'via paiements' : 'via prix vols';

  return (
    <Box sx={{ display: 'flex', flexDirection: 'column', gap: 3 }}>

      {/* KPI cards row */}
      <Grid container spacing={2}>
        <Grid item xs={6} md={3}>
          <KpiCard title="Chiffre d'affaires" value={fmt(revenue?.total ?? 0)} subtitle={sourceLabel} icon={<EuroIcon />} color="#2e7d32" />
        </Grid>
        <Grid item xs={6} md={3}>
          <KpiCard title="Ticket moyen" value={fmt(ticket_moyen ?? 0)} icon={<TrendingUpIcon />} color="#1565c0" />
        </Grid>
        <Grid item xs={6} md={3}>
          <KpiCard title="Nombre de vols" value={revenue?.timeline?.reduce((s: number, t: any) => s + Number(t.count), 0) ?? 0}
            icon={<ConfirmationNumberIcon />} color="#ed6c02" />
        </Grid>
        <Grid item xs={6} md={3}>
          <KpiCard title="Conversion prépaiements"
            value={`${Math.round((prepayment_conversion?.rate ?? 0) * 100)}%`}
            subtitle={`${prepayment_conversion?.used ?? 0} / ${prepayment_conversion?.total ?? 0}`}
            icon={<CardGiftcardIcon />} color="#9c27b0" />
        </Grid>
      </Grid>

      {/* Revenue timeline */}
      <ChartCard title="Évolution du chiffre d'affaires">
        <Chart type="area" height={300} options={{
          ...chartDefaults,
          xaxis: { categories: revenue?.timeline?.map((t: any) => t.period) ?? [] },
          yaxis: { labels: { formatter: (v: number) => fmt(v) } },
          colors: ['#2e7d32'],
          fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05 } },
          tooltip: { y: { formatter: (v: number) => fmt(v) } },
        }}
        series={[{ name: 'CA', data: revenue?.timeline?.map((t: any) => Number(t.value)) ?? [] }]}
        />
      </ChartCard>

      {/* Row: Statuses donut + Statuses timeline */}
      <Grid container spacing={2}>
        <Grid item xs={12} md={5}>
          <ChartCard title="Taux de réservation">
            <Chart type="donut" height={300} options={{
              labels: reservation_statuses?.by_status?.map((s: any) => STATUS_LABELS[s.statut] || s.statut) ?? [],
              colors: reservation_statuses?.by_status?.map((s: any) => STATUS_COLORS[s.statut] || '#999') ?? [],
              legend: { position: 'bottom' },
              plotOptions: { pie: { donut: { size: '55%' } } },
            }}
            series={reservation_statuses?.by_status?.map((s: any) => Number(s.count)) ?? []}
            />
          </ChartCard>
        </Grid>
        <Grid item xs={12} md={7}>
          <ChartCard title="Réservations par période">
            <ReservationTimeline data={reservation_statuses} />
          </ChartCard>
        </Grid>
      </Grid>

      {/* Row: Top circuits + Circuit types */}
      <Grid container spacing={2}>
        <Grid item xs={12} md={7}>
          <ChartCard title="Top circuits">
            <Chart type="bar" height={300} options={{
              ...chartDefaults,
              plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '60%' } },
              xaxis: { categories: top_circuits?.map((c: any) => c.code || c.nom) ?? [] },
              colors: ['#1565c0', '#ed6c02'],
              tooltip: { y: { formatter: (v: number, { seriesIndex }: any) => seriesIndex === 0 ? fmt(v) : `${v} vols` } },
            }}
            series={[
              { name: 'CA', data: top_circuits?.map((c: any) => Number(c.revenue)) ?? [] },
              { name: 'Vols', data: top_circuits?.map((c: any) => Number(c.count)) ?? [] },
            ]}
            />
          </ChartCard>
        </Grid>
        <Grid item xs={12} md={5}>
          <ChartCard title="Types de circuits">
            <Chart type="donut" height={300} options={{
              labels: circuit_types?.map((c: any) => c.nature || 'Non défini') ?? [],
              colors: ['#1565c0', '#2e7d32', '#ed6c02', '#9c27b0', '#d32f2f', '#0288d1', '#795548', '#607d8b'],
              legend: { position: 'bottom' },
              plotOptions: { pie: { donut: { size: '55%' } } },
              tooltip: { y: { formatter: (v: number) => `${v} vols` } },
            }}
            series={circuit_types?.map((c: any) => Number(c.count)) ?? []}
            />
          </ChartCard>
        </Grid>
      </Grid>

      {/* Row: Payment modes + Origines */}
      <Grid container spacing={2}>
        <Grid item xs={12} md={5}>
          <ChartCard title="Modes de paiement">
            <Chart type="donut" height={300} options={{
              labels: payment_modes?.map((p: any) => MODE_LABELS[p.mode] || p.mode) ?? [],
              colors: ['#fb923c', '#34d399', '#38bdf8', '#a78bfa', '#f87171', '#94a3b8'],
              legend: { position: 'bottom' },
              plotOptions: { pie: { donut: { size: '55%' } } },
              tooltip: { y: { formatter: (v: number) => fmt(v) } },
            }}
            series={payment_modes?.map((p: any) => Number(p.total)) ?? []}
            />
          </ChartCard>
        </Grid>
        <Grid item xs={12} md={7}>
          <ChartCard title="Origines">
            <Chart type="bar" height={300} options={{
              ...chartDefaults,
              plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '60%' } },
              xaxis: { categories: origines?.map((o: any) => o.name) ?? [] },
              colors: ['#9c27b0', '#ed6c02'],
              tooltip: { y: { formatter: (v: number, { seriesIndex }: any) => seriesIndex === 0 ? fmt(v) : `${v} résa.` } },
            }}
            series={[
              { name: 'CA', data: origines?.map((o: any) => Number(o.revenue)) ?? [] },
              { name: 'Réservations', data: origines?.map((o: any) => Number(o.count)) ?? [] },
            ]}
            />
          </ChartCard>
        </Grid>
      </Grid>
    </Box>
  );
};

const ChartCard = ({ title, children }: { title: string; children: React.ReactNode }) => (
  <Card sx={{ height: '100%' }}>
    <CardContent>
      <Typography variant="subtitle1" fontWeight={600} gutterBottom>{title}</Typography>
      {children}
    </CardContent>
  </Card>
);

const ReservationTimeline = ({ data }: { data: any }) => {
  const { series, categories } = useMemo(() => {
    if (!data?.timeline?.length) return { series: [], categories: [] };
    const periods = Array.from(new Set(data.timeline.map((t: any) => t.period))) as string[];
    const statuts = Array.from(new Set(data.timeline.map((t: any) => t.statut))) as string[];
    const seriesData = statuts.map(s => ({
      name: STATUS_LABELS[s] || s,
      data: periods.map(p => {
        const match = data.timeline.find((t: any) => t.period === p && t.statut === s);
        return match ? Number(match.count) : 0;
      }),
    }));
    return { series: seriesData, categories: periods };
  }, [data]);

  if (!series.length) return <Typography color="text.secondary">Aucune donnée</Typography>;

  return (
    <Chart type="bar" height={300} options={{
      ...chartDefaults,
      chart: { ...chartDefaults.chart, stacked: true },
      xaxis: { categories },
      colors: Object.values(STATUS_COLORS),
      legend: { position: 'bottom', fontSize: '11px' },
      plotOptions: { bar: { borderRadius: 2, columnWidth: '60%' } },
    }} series={series} />
  );
};
