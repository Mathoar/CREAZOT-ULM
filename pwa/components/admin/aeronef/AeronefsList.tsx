import { type NextPage } from "next";
import {
  Datagrid,
  List,
  TextField,
  TopToolbar,
  NumberField,
  ShowButton,
  SimpleList,
  FunctionField,
  useListContext,
  useUpdate,
  useNotify,
  useRefresh,
  useRecordContext,
} from "react-admin";
import { useMercure } from "../../../utils/mercure";
import { type Circuit } from "../../../types/Circuit";
import { type PagedCollection } from "../../../types/collection";
import { isDefined, isDefinedAndNotVoid } from "../../../app/lib/utils";
import { useMediaQuery, Theme, Button, Chip, ToggleButton, ToggleButtonGroup, Dialog, DialogTitle, DialogContent, DialogContentText, DialogActions, Typography, Box } from '@mui/material';
import { useSessionContext } from "../SessionContextProvider";
import BackupTableIcon from '@mui/icons-material/BackupTable';
import PictureAsPdfIcon from '@mui/icons-material/PictureAsPdf';
import ArchiveIcon from '@mui/icons-material/Archive';
import UnarchiveIcon from '@mui/icons-material/Unarchive';
import WarningAmberIcon from '@mui/icons-material/WarningAmber';
import ToggleAvailabilityButton from "./ToggleAvailabilityButton";
import { useState } from "react";
import { getFormattedValueForBackEnd } from "../../../app/lib/utils";
import { syncOdooDocuments } from "../../../app/lib/client";
import { ProtectedCreateButton, ProtectedEditButton } from "../PermissionGuards";

export interface Props {
  data: PagedCollection<Circuit> | null;
  hubURL: string | null;
  page: number;
}

const CustomCSVButton = ({ isSmall, onClick }) => (
  <Button size="small" color="primary" onClick={() => onClick()} startIcon={<BackupTableIcon className={`${isSmall && 'mb-3'}`}/>}>
    {!isSmall && 'EXPORT CSV'}
  </Button>
);

const CustomPDFButton = ({ isSmall, onClick }) => (
  <Button size="small" color="primary" onClick={() => onClick()} startIcon={<PictureAsPdfIcon className={`${isSmall && 'mb-3'}`}/>}>
    {!isSmall && 'EXPORT PDF'}
  </Button>
);

const ListActions = ({ resource, isSmall, showArchived, setShowArchived }) => {
  const { filterValues } = useListContext();
  const { session } = useSessionContext();
  const isSuperAdmin = session?.user?.roles?.find((r: string) => r === "super_admin");
  const params = new URLSearchParams();

  Object.entries(filterValues).forEach(([key, value]) => {
      if (value && typeof value === 'object' && (value as any).after) {
          if ((value as any).after) params.append(`${key}[after]`, (value as any).after);
          if ((value as any).before) params.append(`${key}[before]`, (value as any).before);
      } else if (value != null) {
          params.append(key, value as string);
      }
  });

  const handleExport = async (format) => {
      const url = `/exports/${resource}?${params.toString()}&format=${format}`;
      const response = await fetch(url, {headers: {'Authorization': `Bearer ${session?.accessToken}`}});
      const blob = await response.blob();
      const blobUrl = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = blobUrl;
      a.download = `${resource}.${format}`;
      a.click();
      window.URL.revokeObjectURL(blobUrl);
  };

  return (
    <TopToolbar sx={{ alignItems: 'center' }}>
        {isSuperAdmin && (
          <ToggleButtonGroup size="small" exclusive value={showArchived ? 'archived' : 'active'}
            onChange={(_, v) => v && setShowArchived(v === 'archived')} sx={{ mr: 1 }}>
            <ToggleButton value="active">Actifs</ToggleButton>
            <ToggleButton value="archived">Archivés</ToggleButton>
          </ToggleButtonGroup>
        )}
        {isSuperAdmin && <ProtectedCreateButton/>}
        <CustomCSVButton onClick={() => handleExport('csv')} isSmall={isSmall}/>
        <CustomPDFButton onClick={() => handleExport('pdf')} isSmall={isSmall}/>
    </TopToolbar>
  );
};

const ArchiveButton = () => {
  const record = useRecordContext();
  const { session } = useSessionContext();
  const [update, { isLoading }] = useUpdate();
  const notify = useNotify();
  const refresh = useRefresh();
  const [open, setOpen] = useState(false);
  const isSuperAdmin = session?.user?.roles?.find((r: string) => r === "super_admin");
  const isArchived = record?.archived;

  if (!isArchived && !isSuperAdmin) {
    return null;
  }

  if (isArchived && !isSuperAdmin) {
    return <Chip label="Archivé" size="small" color="default" variant="outlined" />;
  }

  const handleConfirm = async () => {
    const { documents, createdBy, updatedBy, ...data } = record;
    const documentIds = isDefinedAndNotVoid(documents)
      ? await syncOdooDocuments(documents.map(d => d?.['@id'] ? d : {...d, description: d.title}), 'aeronef', data.id, session)
      : [];

    update('aeronefs', {
      id: record.id,
      data: {
        ...data,
        documents: documentIds,
        archived: !isArchived,
        createdBy: getFormattedValueForBackEnd(createdBy),
        updatedBy: getFormattedValueForBackEnd(updatedBy),
      },
      previousData: record,
    }, {
      onSuccess: () => {
        notify(isArchived
          ? `${record.immatriculation} a été restauré`
          : `${record.immatriculation} a été archivé`, { type: 'success' });
        refresh();
        setOpen(false);
      },
      onError: () => {
        notify("Erreur lors de l'opération", { type: 'error' });
      }
    });
  };

  return (
    <>
      <Button
        size="small"
        startIcon={isArchived ? <UnarchiveIcon /> : <ArchiveIcon />}
        color={isArchived ? 'success' : 'warning'}
        onClick={(e) => { e.stopPropagation(); setOpen(true); }}
        disabled={isLoading}
      >
        {isArchived ? 'Restaurer' : 'Archiver'}
      </Button>

      <Dialog open={open} onClose={() => setOpen(false)} maxWidth="sm" fullWidth
        PaperProps={{ sx: { borderRadius: 3, overflow: 'hidden' } }}>
        <Box sx={{ backgroundColor: isArchived ? '#e8f5e9' : '#fff3e0', px: 3, pt: 3, pb: 2, display: 'flex', alignItems: 'center', gap: 2 }}>
          {isArchived
            ? <UnarchiveIcon sx={{ fontSize: 40, color: '#2e7d32' }} />
            : <WarningAmberIcon sx={{ fontSize: 40, color: '#e65100' }} />}
          <DialogTitle sx={{ p: 0, fontWeight: 700, fontSize: '1.3rem' }}>
            {isArchived ? 'Restaurer cet aéronef ?' : 'Archiver cet aéronef ?'}
          </DialogTitle>
        </Box>
        <DialogContent sx={{ pt: 3 }}>
          <Typography variant="h6" fontWeight={600} gutterBottom>
            {record?.immatriculation}
          </Typography>
          {isArchived ? (
            <DialogContentText>
              L'aéronef sera restauré et réapparaîtra dans les listes, les statistiques
              et sera de nouveau comptabilisé dans le quota d'aéronefs.
            </DialogContentText>
          ) : (
            <>
              <DialogContentText sx={{ mb: 2 }}>
                L'aéronef sera masqué de toutes les listes, des statistiques et ne sera
                plus comptabilisé dans le quota d'aéronefs. Les réservations et vols
                existants resteront rattachés à cet aéronef.
              </DialogContentText>
              <DialogContentText sx={{ fontWeight: 600, color: '#e65100' }}>
                Cette action est irréversible par vous-même. Seul un administrateur
                de la plateforme pourra restaurer cet aéronef.
              </DialogContentText>
            </>
          )}
        </DialogContent>
        <DialogActions sx={{ px: 3, pb: 3 }}>
          <Button onClick={() => setOpen(false)} color="inherit">Annuler</Button>
          <Button onClick={handleConfirm} variant="contained" disabled={isLoading}
            color={isArchived ? 'success' : 'warning'}
            startIcon={isArchived ? <UnarchiveIcon /> : <ArchiveIcon />}>
            {isArchived ? 'Restaurer' : 'Confirmer l\'archivage'}
          </Button>
        </DialogActions>
      </Dialog>
    </>
  );
};

export const AeronefsList: NextPage<Props> = ({ data, hubURL, page }) => {
  const collection = useMercure(data, hubURL);
  const isSmall = useMediaQuery<Theme>(theme => theme.breakpoints.down('sm'));
  const [showArchived, setShowArchived] = useState(false);

  const getDecimalTimeFromLocale = timeToFormat => Math.trunc(timeToFormat) + (timeToFormat - Math.trunc(timeToFormat)) / 60 * 100;
  const getRemainingTime = record => record.decimal ? getRemainingDecimalTime(record) : getRemainingLocaleTime(record);
  const getRemainingMotorTime = record => record.decimal ? getRemainingDecimalTime({entretien: record.changementMoteur, horametre: record.horametre, seuilAlerte: record.seuilAlerteChangementMoteur}) : getRemainingMotorLocaleTime(record);
  const getRemainingLocaleTime = ({entretien, horametre, seuilAlerte}) => getRemainingDecimalTime({entretien : getDecimalTimeFromLocale(entretien), horametre: getDecimalTimeFromLocale(horametre), seuilAlerte});
  const getRemainingMotorLocaleTime = ({changementMoteur, horametre, seuilAlerteChangementMoteur}) => getRemainingDecimalTime({entretien : getDecimalTimeFromLocale(changementMoteur), horametre: getDecimalTimeFromLocale(horametre), seuilAlerte: seuilAlerteChangementMoteur});
  
  const getRemainingDecimalTime = ({entretien, horametre, seuilAlerte}) => {
      const alerte = isDefined(seuilAlerte) ? seuilAlerte : 10;
      const remainingDecimalTime = entretien - horametre;
      const sign = remainingDecimalTime > 0 ? "" : "+ ";
      const intRemainingTime = Math.abs(Math.trunc(remainingDecimalTime));
      const rest = Math.round((Math.abs(remainingDecimalTime) - intRemainingTime) * 60);
      const formattedRest = !isNaN(rest) ? rest < 10 ? "0" + rest.toFixed(0) : rest.toFixed(0) : "-";
      return (
          <p className={`${ (entretien - alerte) - horametre > 0 ? 'font-normal' : 'font-bold'} 
                          ${ (entretien - alerte) - horametre < 0 ? (horametre > entretien ? 'text-red-500' : 'text-orange-500') : 'text-green-500'}`}>
              { (!isNaN(intRemainingTime) ? (sign + intRemainingTime + "h") : "") + formattedRest }
          </p>
      );
  };

  const queryParams = showArchived ? { archived: 'true' } : {};

  return (
    <List resource="aeronefs" actions={<ListActions resource="aeronefs" isSmall={isSmall} showArchived={showArchived} setShowArchived={setShowArchived} />}
      filter={queryParams}>
        { isSmall ? 
            <SimpleList
              primaryText={ record => record.immatriculation }
              secondaryText={ record => record.horametre + 'h' }
              tertiaryText={ record => getRemainingTime(record) }
              linkType="show"
            /> 
            : 
            <Datagrid sx={{
              '& .RaDatagrid-headerCell': { backgroundColor: '#ededed', fontWeight: "lighter" },
              ...(showArchived ? { '& .MuiTableRow-root': { opacity: 0.7 } } : {}),
            }}>
                <FunctionField source="immatriculation" label="Immatriculation" sortable={true}
                  render={record => (
                    <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                      {record.immatriculation}
                      {record.archived && <Chip label="Archivé" size="small" color="default" sx={{ fontSize: '0.7rem' }} />}
                    </Box>
                  )}
                />
                <NumberField source="horametre" options={{ style: 'unit', unit: 'hour' }} label="Horamètre"/>
                <FunctionField
                  source="entretien"
                  label="Entretien"
                  textAlign="right"
                  render={ record => <>{ getRemainingTime(record) }</> }
                />
                <FunctionField
                  source="changementMoteur"
                  label="Changement moteur"
                  textAlign="right"
                  render={ record => <>{ getRemainingMotorTime(record) }</> }
                />
                {!showArchived && <ToggleAvailabilityButton label="Disponibilité" textAlign="center"/>}
                <ArchiveButton />
                <p className="text-right">
                    <ShowButton />
                    <ProtectedEditButton />
                </p>
            </Datagrid>
        }
    </List>
  );
}
