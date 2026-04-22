import { useMemo } from 'react';
import { Box, Grid, Typography, Skeleton, Card, CardContent } from '@mui/material';
import dynamic from 'next/dynamic';
import { KpiCard } from './KpiCard';
import FlightTakeoffIcon from '@mui/icons-material/FlightTakeoff';
import AccessTimeIcon from '@mui/icons-material/AccessTime';
import PeopleIcon from '@mui/icons-material/People';
import AirplanemodeActiveIcon from '@mui/icons-material/AirplanemodeActive';

const Chart = dynamic(() => import('react-apexcharts'), { ssr: false });

const chartDefaults = {
  chart: { toolbar: { show: false }, fontFamily: 'inherit' },
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth' as const, width: 2 },
  grid: { borderColor: '#f0f0f0' },
};

const fmt = (n: number) => new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 }).format(n);
const fmtH = (n: number) => `${Math.round(n * 10) / 10}h`;

interface Props { data: any; loading: boolean; }

export const OperationnelTab = ({ data, loading }: Props) => {
  if (loading || !data) {
    return (
      <Grid container spacing={2}>
        {Array.from({ length: 8 }).map((_, i) => (
          <Grid item xs={12} md={6} lg={3} key={i}><Skeleton variant="rounded" height={140} /></Grid>
        ))}
      </Grid>
    );
  }

  const { flights_timeline, utilization_aircraft, utilization_pilot, revenue_by_aircraft, pilot_hours, reservations_timeline } = data;

  const totalPrestations = flights_timeline?.reduce((s: number, t: any) => s + Number(t.prestations), 0) ?? 0;
  const totalHeures = flights_timeline?.reduce((s: number, t: any) => s + Number(t.heures), 0) ?? 0;
  const totalReservations = reservations_timeline?.reduce((s: number, t: any) => s + Number(t.count), 0) ?? 0;
  const nbPilotes = utilization_pilot?.length ?? 0;

  return (
    <Box sx={{ display: 'flex', flexDirection: 'column', gap: 3 }}>

      <Grid container spacing={2}>
        <Grid item xs={6} md={3}>
          <KpiCard title="Prestations" value={totalPrestations} icon={<FlightTakeoffIcon />} color="#1565c0" />
        </Grid>
        <Grid item xs={6} md={3}>
          <KpiCard title="Heures de vol" value={fmtH(totalHeures)} icon={<AccessTimeIcon />} color="#2e7d32" />
        </Grid>
        <Grid item xs={6} md={3}>
          <KpiCard title="Réservations" value={totalReservations} icon={<AirplanemodeActiveIcon />} color="#ed6c02" />
        </Grid>
        <Grid item xs={6} md={3}>
          <KpiCard title="Pilotes actifs" value={nbPilotes} icon={<PeopleIcon />} color="#9c27b0" />
        </Grid>
      </Grid>

      {/* Flights + Reservations timeline */}
      <ChartCard title="Activité par période">
        <Chart type="line" height={300} options={{
          ...chartDefaults,
          xaxis: { categories: flights_timeline?.map((t: any) => t.period) ?? [] },
          yaxis: [
            { title: { text: 'Prestations' }, seriesName: 'Prestations' },
            { opposite: true, title: { text: 'Heures' }, seriesName: 'Heures de vol' },
          ],
          colors: ['#1565c0', '#2e7d32', '#ed6c02'],
          tooltip: { shared: true },
        }}
        series={[
          { name: 'Prestations', type: 'column', data: flights_timeline?.map((t: any) => Number(t.prestations)) ?? [] },
          { name: 'Heures de vol', type: 'line', data: flights_timeline?.map((t: any) => Math.round(Number(t.heures) * 10) / 10) ?? [] },
        ]}
        />
      </ChartCard>

      {/* Utilization: Aircraft + Pilot */}
      <Grid container spacing={2}>
        <Grid item xs={12} md={6}>
          <ChartCard title="Utilisation par aéronef">
            <Chart type="bar" height={300} options={{
              ...chartDefaults,
              plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '55%' } },
              xaxis: { categories: utilization_aircraft?.map((a: any) => a.immatriculation) ?? [] },
              colors: ['#1565c0', '#2e7d32'],
              tooltip: { y: { formatter: (v: number, { seriesIndex }: any) => seriesIndex === 0 ? `${v} prest.` : fmtH(v) } },
            }}
            series={[
              { name: 'Prestations', data: utilization_aircraft?.map((a: any) => Number(a.prestations)) ?? [] },
              { name: 'Heures', data: utilization_aircraft?.map((a: any) => Math.round(Number(a.heures) * 10) / 10) ?? [] },
            ]}
            />
          </ChartCard>
        </Grid>
        <Grid item xs={12} md={6}>
          <ChartCard title="Utilisation par pilote">
            <Chart type="bar" height={300} options={{
              ...chartDefaults,
              plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '55%' } },
              xaxis: { categories: utilization_pilot?.map((p: any) => p.pilote) ?? [] },
              colors: ['#9c27b0', '#ed6c02'],
              tooltip: { y: { formatter: (v: number, { seriesIndex }: any) => seriesIndex === 0 ? `${v} prest.` : fmtH(v) } },
            }}
            series={[
              { name: 'Prestations', data: utilization_pilot?.map((p: any) => Number(p.prestations)) ?? [] },
              { name: 'Heures', data: utilization_pilot?.map((p: any) => Math.round(Number(p.heures) * 10) / 10) ?? [] },
            ]}
            />
          </ChartCard>
        </Grid>
      </Grid>

      {/* Revenue by aircraft + Pilot remuneration */}
      <Grid container spacing={2}>
        <Grid item xs={12} md={6}>
          <ChartCard title="CA par aéronef">
            <Chart type="bar" height={300} options={{
              ...chartDefaults,
              plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '55%' } },
              xaxis: { categories: revenue_by_aircraft?.map((a: any) => a.immatriculation) ?? [] },
              colors: ['#2e7d32', '#d32f2f'],
              tooltip: { y: { formatter: (v: number) => fmt(v) } },
            }}
            series={[
              { name: 'CA', data: revenue_by_aircraft?.map((a: any) => Number(a.revenue)) ?? [] },
              { name: 'Coût', data: revenue_by_aircraft?.map((a: any) => Number(a.cout)) ?? [] },
            ]}
            />
          </ChartCard>
        </Grid>
        <Grid item xs={12} md={6}>
          <ChartCard title="Rémunération pilotes (coût circuit)">
            <Chart type="bar" height={300} options={{
              ...chartDefaults,
              plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '55%' } },
              xaxis: { categories: pilot_hours?.map((p: any) => p.pilote) ?? [] },
              colors: ['#ed6c02', '#1565c0'],
              tooltip: { y: { formatter: (v: number, { seriesIndex }: any) => seriesIndex === 0 ? fmt(v) : `${v} vols` } },
            }}
            series={[
              { name: 'Rémunération', data: pilot_hours?.map((p: any) => Math.round(Number(p.remuneration) * 100) / 100) ?? [] },
              { name: 'Vols', data: pilot_hours?.map((p: any) => Number(p.vols)) ?? [] },
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
