import { TableRow, TableCell, TableFooter, Box } from '@mui/material';
import React, { useEffect, useState } from 'react';
import { type NextPage } from "next";
import {
  TextInput,
  Datagrid,
  DatagridBody,
  List,
  TextField,
  ExportButton,
  TopToolbar,
  DateField,
  NumberField,
  DateInput,
  ShowButton,
  ArrayField,
  SimpleList,
  FunctionField,
  useListContext,
  Form,
  Button as ReactAdminButton,
  BulkDeleteButton,
} from "react-admin";
import Button from '@mui/material/Button';
import { Fragment } from 'react';
import { isDefined, toLocalDateString } from "../../../app/lib/utils";
import { useMediaQuery, Theme } from '@mui/material';
import { useClient } from '../ClientProvider';
import FilterListIcon from '@mui/icons-material/FilterList';
import { clientWithOptions } from "../../../app/lib/client";
import { type Prestation } from "../../../types/Prestation";
import { type PagedCollection } from "../../../types/collection";
import { useSessionContext } from "../../admin/SessionContextProvider";
import BackupTableIcon from '@mui/icons-material/BackupTable';
import PictureAsPdfIcon from '@mui/icons-material/PictureAsPdf';
import { ProtectedCreateButton, ProtectedEditButton } from "../PermissionGuards";
import { usePermissions } from "../PermissionProvider";
import { useSession } from "next-auth/react";
import { Dialog, DialogTitle, DialogContent, DialogActions, TextField as MuiTextField, Typography, Alert } from '@mui/material';
import BuildIcon from '@mui/icons-material/Build';
import { useNotify, useRefresh } from 'react-admin';

const API_DOMAIN = process.env.NEXT_PUBLIC_API_DOMAIN || '';

export interface Props {
  data: PagedCollection<Prestation> | null;
  hubURL: string | null;
  page: number;
}

const CustomCSVButton = ({ isSmall, onClick }) => {
  return (
    <Button
      size="small"
      color="primary"
      onClick={() => onClick()}
      startIcon={<BackupTableIcon className={`${isSmall && 'mb-3'}`}/>}
    >
      {!isSmall && 'EXPORT CSV'}
    </Button>
  );
};

const CustomPDFButton = ({ isSmall, onClick }) => {
  return (
    <Button
      size="small"
      color="primary"
      onClick={() => onClick()}
      startIcon={<PictureAsPdfIcon className={`${isSmall && 'mb-3'}`}/>}
    >
      {!isSmall && 'EXPORT PDF'}
    </Button>
  );
};

const CustomListActions = ({ showMore, setShowMore, isSmall, resource }) => {
  
  const { filterValues } = useListContext();
  const { session } = useSessionContext();
  const params = new URLSearchParams();

  Object.entries(filterValues).forEach(([key, value]) => {
      // @ts-ignore
      if (value && typeof value === 'object' && value.after) {
          // @ts-ignore
          if (value.after) params.append(`${key}[after]`, value.after);
          // @ts-ignore
          if (value.before) params.append(`${key}[before]`, value.before);
      } else if (value != null) {
          // @ts-ignore
          params.append(key, value);
      }
  });

  const handleExport = async (format) => {

      const url = `/exports/${resource}?${params.toString()}&format=${format}`;
      const clientHeaders = (() => { try { const c = JSON.parse(sessionStorage.getItem('client') || '{}'); return c?.id ? { 'X-Client-Id': String(c.id) } : {}; } catch(e) { return {}; } })();
      const response = await fetch(url, {headers: {'Authorization': `Bearer ${session?.accessToken}`, ...clientHeaders}});

      const blob = await response.blob();
      const blobUrl = window.URL.createObjectURL(blob);

      const a = document.createElement('a');
      a.href = blobUrl;
      a.download = `${resource}.${format}`;
      a.click();
      window.URL.revokeObjectURL(blobUrl);
  };

  return (
    <TopToolbar>
      <CustomFilterButton showMore={showMore} setShowMore={setShowMore} isSmall={isSmall}/>
      <ProtectedCreateButton className={`${!isSmall && 'mb-[2px]'}`}/>
      <CustomCSVButton onClick={ () => handleExport('csv') } isSmall={isSmall}/>
      <CustomPDFButton onClick={ () => handleExport('pdf') } isSmall={isSmall}/>
    </TopToolbar>
  );
};

const formatHeure = (totalMinutes) => {
  const heures = Math.floor(totalMinutes / 60);
  const minutes = Math.round(totalMinutes % 60);
  return `${String(heures).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
};

const getFormattedDuration = ({ aeronef, duree }) => {
  const hours = Math.trunc(duree);
  const minutes = aeronef.decimal ? Math.round((duree - Math.trunc(duree)) * 60) : Math.round((duree - Math.trunc(duree)) * 100);
  return `${hours}:${minutes < 10 ? '0' : ''}${minutes}`;
};

const getFormattedHorametre = (prestation, horametre) => {
  const hours = Math.trunc(prestation[horametre]);
  const minutes = Math.round((prestation[horametre] - Math.trunc(prestation[horametre])) * (prestation.aeronef.decimal ? 10 : 100));
  return `${hours}${prestation.aeronef.decimal ? ',' : ':'}${!prestation.aeronef.decimal && minutes < 10 ? '0' : ''}${minutes}`;
};

const CustomFilterBar = ({ showMore, isSmall }) => {

    const { filterValues, setFilters } = useListContext();
    const [formValues, setFormValues] = useState({
        'date[after]': filterValues['date[after]'] ? toLocalDateString(new Date(filterValues['date[after]'])) : '',
        'date[before]': filterValues['date[before]'] ? toLocalDateString(new Date(filterValues['date[before]'])) : '',
        'pilote.firstName': filterValues['pilote.firstName'] || '',
        'aeronef.immatriculation': filterValues['aeronef.immatriculation'] || '',
    });

    useEffect(() => {
        setFormValues({
            'date[after]': filterValues['date[after]'] ? toLocalDateString(new Date(filterValues['date[after]'])) : '',
            'date[before]': filterValues['date[before]'] ? toLocalDateString(new Date(filterValues['date[before]'])) : '',
            'pilote.firstName': filterValues['pilote.firstName'] || '',
            'aeronef.immatriculation': filterValues['aeronef.immatriculation'] || '',
        });
    }, [filterValues]);
  
    const handleChange = (e) => {
        const { name, value } = e.target;
        const newValues = { ...formValues, [name]: value };
        setFormValues(newValues);
        setFilters(newValues); 
    };
  
    return !showMore ? <></> :
      <Form >
          <Box display="flex" flexWrap="wrap" columnGap={isSmall ? 6 : 2} rowGap={0.5} mt={1} alignItems="flex-end">
              <TextInput
                  source="pilote.firstName"
                  label="Pilote"
                  onChange={handleChange}
                  defaultValue={formValues['pilote.firstName']}
                  sx={{ width: isSmall ? '100%' : 200 }}
              />
              <TextInput
                  source="aeronef.immatriculation"
                  label="Aéronef"
                  onChange={handleChange}
                  defaultValue={formValues['aeronef.immatriculation']}
                  sx={{ width: isSmall ? '100%' : 200 }}
              />
              <DateInput
                  source="date[after]"
                  label="Date Min"
                  onChange={handleChange}
                  defaultValue={formValues['date[after]']}
                  sx={{ width: isSmall ? '100%' : 200 }}
              />
              <DateInput
                  source="date[before]"
                  label="Date Max"
                  onChange={handleChange}
                  defaultValue={formValues['date[before]']}
                  sx={{ width: isSmall ? '100%' : 200 }}
              />
          </Box>
      </Form>
  };

const VolsExpansion = ({ client }) => {

  const OptionField = () => !clientWithOptions(client) ? <></> : <TextField source="option.nom" label="Option" />

  return (
    <ArrayField source="vols">
      <Datagrid isRowSelectable={record => false} rowClick={false} bulkActionButtons={false} sx={{ '& .RaDatagrid-headerCell': { backgroundColor: '#ededed', fontWeight: "lighter" } }} className="text-xs italic">
        <NumberField source="quantite" label="Nb vol(s)" />
        <FunctionField
          source="circuit"
          render={record => isDefined(record.circuit) && <p>{record.circuit.code} - <span className="text-xs italic">{record.circuit.nom}</span></p>}
        />
        <FunctionField
          source="nature"
          render={record => isDefined(record.circuit) && isDefined(record.circuit.nature) && <p>{record.circuit.nature.code} - <span className="text-xs italic">{record.circuit.nature.label}</span></p>}
        />
        <OptionField />
      </Datagrid>
    </ArrayField>
  );
};

const CustomFilterButton = ({ showMore, setShowMore, isSmall }) => {
  return (
    <Button
      size="small"
      color="primary"
      onClick={() => setShowMore(!showMore)}
      startIcon={<FilterListIcon className={`${isSmall && 'mb-3'}`}/>}
    >
      {!isSmall && 'FILTRER'}
    </Button>
  );
};

const CustomBody = ({ isAdmin, ...props }) => {
  const { data, isLoading } = useListContext();

  if (isLoading || !data) return null;

  const totalMinutes = React.useMemo(() => {
    return data.reduce((sum, record) => {
      const duree = parseFloat(record.duree) || 0;
      const hours = Math.trunc(duree);
      const frac = duree - hours;
      const minutes = record.aeronef?.decimal ? Math.round(frac * 60) : Math.round(frac * 100);
      return sum + hours * 60 + minutes;
    }, 0);
  }, [data]);

  return (
    <Fragment>
      <DatagridBody {...props}/>
      <TableFooter>
        <TableRow sx={{ backgroundColor: '#ededed', fontStyle: 'italic', fontWeight: 'bold', color: '#555' }}>
          <TableCell colSpan={isAdmin ? 6 : 5} sx={{ fontStyle: 'italic', fontWeight: 'bold', color: '#555' }}>
            Total
          </TableCell>
          <TableCell sx={{ fontStyle: 'italic', fontWeight: 'bold', color: '#555', textAlign: 'right' }}>
            {formatHeure(totalMinutes)}
          </TableCell>
          <TableCell />
          <TableCell />
          {isAdmin && <TableCell /> }
        </TableRow>
      </TableFooter>
    </Fragment>
  );
};

const MobileFooter = (props) => {
    const { data, isLoading } = useListContext();
  
    if (isLoading || !data) return null;
  
    const totalMinutes = React.useMemo(() => {
        return data.reduce((sum, record) => {
          const duree = parseFloat(record.duree) || 0;
          const hours = Math.trunc(duree);
          const frac = duree - hours;
          const minutes = record.aeronef?.decimal ? Math.round(frac * 60) : Math.round(frac * 100);
          return sum + hours * 60 + minutes;
        }, 0);
    }, [data]);

    return (
      <div style={{
          padding: '0.5em 1em',
          background: '#ededed',
          fontSize: '0.9em',
          fontWeight: 'bolder',
          display: 'flex',
          justifyContent: 'space-between'
      }}>
          <span>{`Total`}</span>
          <span>{`${ formatHeure(totalMinutes) }`}</span>
      </div>
    );
};

const InlineCorrectTrigger = ({ record, onOpen }: { record: any, onOpen: (record: any) => void }) => {
  if (!record) return null;
  const handleClick = (e) => {
    e.stopPropagation();
    onOpen(record);
  };
  return (
    <Button size="small" onClick={handleClick} sx={{ minWidth: 'auto', p: 0.5 }} title="Corriger l'horamètre">
      <BuildIcon fontSize="small" />
    </Button>
  );
};

const CorrectHorametreDialog = ({ open, record, onClose }: { open: boolean, record: any, onClose: () => void }) => {
  const notify = useNotify();
  const refresh = useRefresh();
  const { client } = useClient();
  const { data: session } = useSession() as any;
  const [value, setValue] = useState('');
  const [loading, setLoading] = useState(false);

  React.useEffect(() => {
    if (open && record) setValue(String(record.horametreFin));
  }, [open, record]);

  if (!record) return null;

  const isDecimal = record.aeronef?.decimal;
  const formatH = (val: number) => {
    const hours = Math.trunc(val);
    const minutes = Math.round((val - Math.trunc(val)) * (isDecimal ? 10 : 100));
    return `${hours}${isDecimal ? ',' : ':'}${!isDecimal && minutes < 10 ? '0' : ''}${minutes}`;
  };

  const handleSubmit = async () => {
    const parsed = parseFloat(value.replace(',', '.').replace(':', '.'));
    if (isNaN(parsed) || parsed <= record.horametreDepart) {
      notify("L'horamètre de fin doit être supérieur à l'horamètre de départ", { type: 'error' });
      return;
    }
    setLoading(true);
    try {
      const headers: Record<string, string> = {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${session?.accessToken}`,
      };
      if (client?.id) headers['X-Client-Id'] = String(client.id);
      const prestationId = typeof record.id === 'string' && record.id.includes('/') ? record.id.split('/').pop() : record.id;
      const res = await fetch(`${API_DOMAIN}/admin/prestation/${prestationId}/correct-horametre`, { method: 'POST', headers, body: JSON.stringify({ horametreFin: parsed }) });
      if (!res.ok) { const err = await res.json(); throw new Error(err.error || 'Erreur serveur'); }
      notify('Horamètre corrigé', { type: 'success' });
      onClose();
      refresh();
    } catch (e: any) {
      notify(e.message || 'Erreur', { type: 'error' });
    } finally {
      setLoading(false);
    }
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="xs" fullWidth onClick={(e) => e.stopPropagation()} onMouseDown={(e) => e.stopPropagation()}>
      <DialogTitle>Correction de l'horamètre de fin</DialogTitle>
      <DialogContent>
        <Alert severity="info" sx={{ mb: 2 }}>Met à jour la durée de la prestation et l'horamètre de l'aéronef.</Alert>
        <Typography variant="body2" sx={{ mb: 1 }}>Horamètre de départ : <strong>{formatH(record.horametreDepart)}</strong></Typography>
        <Typography variant="body2" sx={{ mb: 2 }}>Horamètre de fin actuel : <strong>{formatH(record.horametreFin)}</strong></Typography>
        <MuiTextField label="Nouvel horamètre de fin" value={value} onChange={(e) => setValue(e.target.value)} fullWidth autoFocus helperText={isDecimal ? "Format décimal (ex: 1234,5)" : "Format conventionnel (ex: 1234.30)"} />
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose} disabled={loading}>Annuler</Button>
        <Button onClick={handleSubmit} variant="contained" disabled={loading}>{loading ? 'Correction...' : 'Corriger'}</Button>
      </DialogActions>
    </Dialog>
  );
};

const PrestationBulkActions = () => (
  <BulkDeleteButton
    mutationMode="pessimistic"
    confirmTitle="Supprimer les prestations sélectionnées ?"
    confirmContent="L'horamètre des aéronefs et les heures de vol des pilotes concernés seront automatiquement corrigés."
  />
);

const CustomDatagrid = ({isAdmin, client}) => {
  const { canWrite } = usePermissions();
  const [correctRecord, setCorrectRecord] = useState<any>(null);

  return (  
    <>
      <Datagrid body={(props) => <CustomBody {...props} isAdmin={isAdmin}/>} bulkActionButtons={ isAdmin ? <PrestationBulkActions /> : false } expand={<VolsExpansion client={client}/>} sx={{ '& .RaDatagrid-expandedPanel': { backgroundColor: '#ededed' }, '& .RaDatagrid-tbody': { backgroundColor: '#FFFFFF' }, '& .RaDatagrid-headerCell': { backgroundColor: '#ededed' } }}>
        <DateField source="date" sortable={true} />
        <TextField source="aeronef.immatriculation" label="Aéronef" sortable={true} />
        <FunctionField
          label="Pilote(s)"
          source="pilote.firstName"
          sortable={true}
          render={(record) => <span>
            {isDefined(record?.pilotName) && record?.pilotName !== '' ? record?.pilotName : ''}
            {isDefined(record?.encadrantName) && record?.encadrantName !== '' ?
              <span className="text-gray-500 italic text-xs"><br />{record?.encadrantName}</span> : ''}
          </span>}
        />
        <FunctionField
          source="horametreDepart"
          label="Horamètre au Départ"
          render={record => getFormattedHorametre(record, "horametreDepart")}
          textAlign="right"
        />
        <FunctionField
          source="duree"
          label="Durée"
          render={record => getFormattedDuration(record)}
          textAlign="right"
        />
        <FunctionField
          source="horametreFin"
          label="Horamètre à l'arrivée"
          render={record => getFormattedHorametre(record, "horametreFin")}
          textAlign="right"
        />
        <TextField source="remarques" label="Remarques" />
        {isAdmin &&
          <FunctionField render={(record) => (
            <p className="text-right" style={{ whiteSpace: 'nowrap' }}>
              <ShowButton label="" />
              <ProtectedEditButton label="" />
              {canWrite('vols') && <InlineCorrectTrigger record={record} onOpen={setCorrectRecord} />}
            </p>
          )} />
        }
      </Datagrid>
      <CorrectHorametreDialog open={!!correctRecord} record={correctRecord} onClose={() => setCorrectRecord(null)} />
    </>
  );
};

const ListContent = ({ isSmall, isAdmin, client }) => {

  return isSmall ?
      <>
        <SimpleList
          primaryText={
            record => <>
              {record?.aeronefImmatriculation + ' | ' + (isDefined(record?.pilotName) && record?.pilotName !== '' ? record?.pilotName : '')}
              {isDefined(record?.encadrantName) && record?.encadrantName !== '' ? <span className="text-gray-500 italic text-sm"> - {record?.encadrantName}</span> : ''}
            </>
          }
          // @ts-ignore
          secondaryText={record => `${(new Date(record?.date)).toLocaleDateString("fr-FR", { year: "numeric", month: "numeric", day: "numeric" })}`}
          tertiaryText={record => getFormattedDuration(record)}
          linkType="show"
        />
        <MobileFooter/>
      </>
    :
    <CustomDatagrid isAdmin={isAdmin} client={client}/>;
};

export const PrestationsList: NextPage<Props> = ({ data, hubURL, page }) => {
  const { client } = useClient();
  const { session } = useSessionContext();
  const isSmall = useMediaQuery<Theme>(theme => theme.breakpoints.down('sm'));
  const user = session?.user;
  // @ts-ignore
  const isAdmin = isDefined(session) && isDefined(user) && user?.roles.includes("admin");
  const defaultFilters = {};

  const [showMore, setShowMore] = useState(false);
  const [filters, setFilters] = useState(defaultFilters);

  return (
    <List
      key="prestations-list"
      resource="prestations"
      actions={<CustomListActions showMore={showMore} setShowMore={setShowMore} isSmall={isSmall} resource="prestations"/>}
      filters={<CustomFilterBar showMore={showMore} isSmall={isSmall}/>}
      // @ts-ignore
      filterValues={filters}
      filterDefaultValues={defaultFilters}
      disableSyncWithLocation
    >
      <ListContent isSmall={isSmall} isAdmin={isAdmin} client={client}/>
    </List>
  );
};