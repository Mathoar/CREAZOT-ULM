import React from "react";
import { ArrayInput, DateInput, Edit, NumberInput, ReferenceInput, SimpleForm, SimpleFormIterator, TextInput, SelectInput, useDataProvider, TopToolbar, ShowButton, useRecordContext, useNotify, useRefresh, Button as ReactAdminButton } from "react-admin";
import { getFormattedValueForBackEnd, isDefined, isDefinedAndNotVoid, isValid } from "../../../app/lib/utils";
import { useClient } from '../../admin/ClientProvider';
import { clientWithLandingManagement, clientWithOptions, getAirportCode, getAirportName, getDefaultLanding } from "../../../app/lib/client";
import { useWatch, useFormContext } from "react-hook-form";
import { useEffect, useState } from "react";
import { Box, Alert, Dialog, DialogTitle, DialogContent, DialogActions, TextField as MuiTextField, Typography, Button as MuiButton } from "@mui/material";
import BuildIcon from '@mui/icons-material/Build';
import { usePermissions } from "../PermissionProvider";
import { useSession } from "next-auth/react";

const FilteredPiloteInput = ({ pilotes, circuits }) => {
  const vols = useWatch({ name: "vols" });
  const date = useWatch({ name: "date" });
  const { setValue, getValues } = useFormContext();
  const selectedPiloteId = getValues("pilote.@id");
  
  let needsEncadrant = false;
  let qualificationsRequises = [];
  if (isDefinedAndNotVoid(vols) && isDefinedAndNotVoid(circuits)) {
    vols.forEach(({ circuit }) => {
      const selectedCircuit = circuits.find(c => c['@id'] === circuit['@id']);
      qualificationsRequises = [...qualificationsRequises, ...selectedCircuit?.qualifications?.map(q => q['@id']) || []];
      needsEncadrant = selectedCircuit?.nature?.needsEncadrant === true ? true : needsEncadrant;
    });
  }

  const pilotesEligibles = qualificationsRequises.length === 0
    ? pilotes.filter(({profil, ...p}) => isValid(profil?.certificatMedical?.validUntil, profil?.certificatMedical?.dateObtention, date))
    : pilotes.filter(({profil, ...p}) =>
        isValid(profil?.certificatMedical?.validUntil, profil?.certificatMedical?.dateObtention, date) && 
        Array.isArray(profil.pilotQualifications) &&
        profil.pilotQualifications
              .filter(q => isValid(q.validUntil, q.dateObtention, date))
              .map(q => q.qualification['@id'])
              .some(q => qualificationsRequises.includes(q))
  );

  useEffect(() => {
    if (isDefinedAndNotVoid(pilotes) && isDefined(selectedPiloteId)) {
      const stillEligible = pilotesEligibles.some(p => p['@id'] === selectedPiloteId);
      if (!stillEligible)
        setValue("pilote.@id", null);
    }
  }, [vols, pilotesEligibles, selectedPiloteId]);
  
  return (
    <SelectInput
      source="pilote.@id"
      label="Pilote @id"
      choices={ pilotesEligibles }
      optionText={(r) => isDefined(r) && isDefined(r.firstName) ? r.firstName.charAt(0).toUpperCase() + r.firstName.slice(1) : ' '}
      optionValue="@id"
    />
  );
};

const EncadrantInput = ({ pilotes, circuits }) => {
  const { setValue, getValues } = useFormContext();
  const date = useWatch({ name: "date" });
  const vols = useWatch({ name: "vols" });
  const piloteId = useWatch({ name: "pilote.@id" });
  const currentId = useWatch({ name: "encadrant.@id" });
  const currentEncadrant = pilotes.find(p => p["@id"] === currentId);

  let needsEncadrant = false;
  let encadrantIsOptional = false;
  if (isDefinedAndNotVoid(vols) && isDefinedAndNotVoid(circuits)) {
    vols.forEach(({ circuit }) => {
      const selectedCircuit = circuits.find(c => c['@id'] === circuit['@id']);
      if (selectedCircuit?.nature?.needsEncadrant === true || selectedCircuit?.needsEncadrant === true) {
        needsEncadrant = true;
      }
      if (selectedCircuit?.nature?.encadrantOptional === true) {
        encadrantIsOptional = true;
      }
    });
  }
  const encadrantEnabled = needsEncadrant || encadrantIsOptional;

  const encadrants = React.useMemo(() => {
    return (pilotes || []).filter(p =>
      isValid(p?.profil?.certificatMedical?.validUntil, p?.profil?.certificatMedical?.dateObtention, date) &&
      p?.profil?.pilotQualifications?.some(q =>
        q?.qualification?.encadrant && isValid(q.validUntil, q.dateObtention, date)
      )
    );
  }, [pilotes, date]);

  const choices = React.useMemo(() => {
    if (!currentEncadrant) return encadrants;
    return [...encadrants, currentEncadrant].filter(
      (v, i, arr) => arr.findIndex(x => x["@id"] === v["@id"]) === i
    );
  }, [encadrants, currentEncadrant]);


  useEffect(() => {
    if (!encadrantEnabled) setValue("encadrant.@id", null);
  }, [encadrantEnabled, setValue]);

  useEffect(() => {
    if (needsEncadrant && !isDefined(currentId) && isDefinedAndNotVoid(encadrants)) { 
      const selectedPilote = pilotes.find(p => p["@id"] === piloteId);
      const pilotePeutEncadrer = !!selectedPilote?.profil?.pilotQualifications?.some(q =>
        q?.qualification?.encadrant && isValid(q.validUntil, q.dateObtention, date)
      );

      if (!pilotePeutEncadrer) {
        setValue("encadrant.@id", encadrants[0]["@id"], { shouldValidate: true });
      }
    }
  }, [needsEncadrant, encadrants, pilotes, piloteId, date, setValue, getValues]);

  return (
    <SelectInput
      source="encadrant.@id"
      label="Encadrant @id"
      choices={choices}
      disabled={!encadrantEnabled}
      optionText={r =>
        r?.firstName ? r.firstName[0].toUpperCase() + r.firstName.slice(1) : " "
      }
      optionValue="@id"
    />
  );
};

const LandingsInput = ({ client }) => {
    const vols = useWatch({ name: "vols" });
    const airportList = client.airports.map(a =>({...a, airportCode: getAirportCode(a), airportName: a.nom}));

    const validateLandings = (value) => {
      const codes = new Set();
      for (const l of value || []) {
        if (codes.has(l.airportCode)) {
          return 'Un même aéroport ne peut être déclaré plusieurs fois.';
        }
        if (l.touches === 0 && l.complets === 0) {
          return 'Au moins un toucher ou un complet doit être déclaré.';
        }
        codes.add(l.airportCode);
      }
      return undefined;
    };

    if (!clientWithLandingManagement(client) || !isDefinedAndNotVoid(client.airports) || !vols || !Array.isArray(vols) )
      return null;

    return (
      <ArrayInput source="landings" label="Atterrissages" validate={validateLandings}>
          <SimpleFormIterator inline disableReordering> 
              <SelectInput
                  source="airportCode"
                  label="Aéroport"
                  choices={ airportList }
                  optionText={(a) => a.airportName}
                  optionValue="airportCode"
                  />
              <NumberInput source="touches" label="Touchés" min="0" defaultValue={ 0 }/>
              <NumberInput source="complets" label="Complets" min="0" defaultValue={ 1 }/>
          </SimpleFormIterator>
      </ArrayInput>
    );
};

const OptionInput = ({ client }) => {
  return !clientWithOptions(client) ? null :
      <Box flex={1}>
        <ReferenceInput reference="options" source="option.@id" label="Option" />
      </Box>
};

const API_DOMAIN = process.env.NEXT_PUBLIC_API_DOMAIN || '';

const EditCorrectHorametreButton = () => {
    const record = useRecordContext();
    const notify = useNotify();
    const refresh = useRefresh();
    const { client } = useClient();
    const { data: session } = useSession() as any;
    const [open, setOpen] = useState(false);
    const [value, setValue] = useState('');
    const [loading, setLoading] = useState(false);

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
            const headers: Record<string, string> = { 'Content-Type': 'application/json', Authorization: `Bearer ${session?.accessToken}` };
            if (client?.id) headers['X-Client-Id'] = String(client.id);
            const id = typeof record.id === 'string' && record.id.includes('/') ? record.id.split('/').pop() : record.id;
            const res = await fetch(`${API_DOMAIN}/admin/prestation/${id}/correct-horametre`, { method: 'POST', headers, body: JSON.stringify({ horametreFin: parsed }) });
            if (!res.ok) { const err = await res.json(); throw new Error(err.error || 'Erreur serveur'); }
            notify('Horamètre corrigé avec succès', { type: 'success' });
            setOpen(false);
            refresh();
        } catch (e: any) {
            notify(e.message || 'Erreur', { type: 'error' });
        } finally {
            setLoading(false);
        }
    };

    return (
        <>
            <ReactAdminButton label="Corriger l'horamètre" onClick={() => { setValue(String(record.horametreFin)); setOpen(true); }}>
                <BuildIcon />
            </ReactAdminButton>
            <Dialog open={open} onClose={() => setOpen(false)} maxWidth="xs" fullWidth>
                <DialogTitle>Correction de l'horamètre de fin</DialogTitle>
                <DialogContent>
                    <Alert severity="info" sx={{ mb: 2 }}>Cette action met à jour la durée, l'horamètre de l'aéronef et les heures de vol du pilote.</Alert>
                    <Typography variant="body2" sx={{ mb: 1 }}>Horamètre de départ : <strong>{formatH(record.horametreDepart)}</strong></Typography>
                    <Typography variant="body2" sx={{ mb: 2 }}>Horamètre de fin actuel : <strong>{formatH(record.horametreFin)}</strong></Typography>
                    <MuiTextField label="Nouvel horamètre de fin" value={value} onChange={(e) => setValue(e.target.value)} fullWidth autoFocus helperText={isDecimal ? "Format décimal (ex: 1234,5)" : "Format conventionnel (ex: 1234.30)"} />
                </DialogContent>
                <DialogActions>
                    <MuiButton onClick={() => setOpen(false)} disabled={loading}>Annuler</MuiButton>
                    <MuiButton onClick={handleSubmit} variant="contained" disabled={loading}>{loading ? 'Correction...' : 'Corriger'}</MuiButton>
                </DialogActions>
            </Dialog>
        </>
    );
};

const PrestationEditActions = () => {
    const { canWrite } = usePermissions();
    return (
        <TopToolbar>
            <ShowButton />
            {canWrite('vols') && <EditCorrectHorametreButton />}
        </TopToolbar>
    );
};

export const PrestationEdit = () => {

    const { client } = useClient();
    const dataProvider = useDataProvider();
    const [pilotes, setPilotes] = useState([]);
    const [circuits, setCircuits] = useState([]);
    const defaultLanding = getDefaultLanding(client);

    useEffect(() => {
      getProfilPilotes();
      getCircuits();
    }, []);

    const getProfilPilotes = () => {
        dataProvider
            .getList("profil_pilotes", { filter: { "exists[certificatMedical]": true } })
            .then(({ data }) => {
                const piloteProfils = data
                .filter(p => isDefined(p.pilote))
                .map(({pilote, ...profil}) => ({
                  ...pilote, 
                  profil: {...profil, pilotQualifications: isDefinedAndNotVoid(profil.pilotQualifications) ? profil.pilotQualifications : []},
                }))
              setPilotes(piloteProfils)
            });
    };

    const getCircuits = () => {
      dataProvider
            .getList("circuits", {})
            .then(({ data }) => setCircuits(data));
    };

    const transform = ({date, aeronef, pilote, encadrant, vols, createdBy, updatedBy, ...data}) => {
        const transformedVols = [];
        let landingAssigned = false;

        for (const vol of vols) {
          const transformedVol = {
            ...vol,
            circuit: getFormattedValueForBackEnd(vol.circuit),
            option: getFormattedValueForBackEnd(vol.option, clientWithOptions(client)),
            createdBy: getFormattedValueForBackEnd(vol.createdBy),
            updatedBy: getFormattedValueForBackEnd(vol.updatedBy),
          };
      
          if (clientWithLandingManagement(client)) {
            if (!landingAssigned && vol.circuit?.requireLandingDeclaration && isDefinedAndNotVoid(vol.landings)) {
              // @ts-ignore
              transformedVol.landings = vol.landings.map(({id, airportCode, ...l}) => {
                const formattedLanding =  {...l, airportCode, airportName: getAirportName(client, airportCode), complets: parseInt(l.complets ?? 0, 10), touches: parseInt(l.touches ?? 0, 10)};
                return '@id' in formattedLanding && isDefined(formattedLanding['@id']) ? {...formattedLanding, id} : formattedLanding;
              }
              );
              landingAssigned = true;
            } else {
                if (isDefinedAndNotVoid(vol.landings)) {
                  // @ts-ignore
                  transformedVol.landings = vol.landings.map(({id, airportCode, ...l}) => {
                    const formattedLanding =  {...l, airportCode, airportName: getAirportName(client, airportCode), complets: parseInt(l.complets ?? 0, 10), touches: parseInt(l.touches ?? 0, 10)};
                    return '@id' in formattedLanding && isDefined(formattedLanding['@id']) ? {...formattedLanding, id} : formattedLanding;
                  });
                } else {
                  if (isDefined(vol.circuit?.hadDefaultLanding) && vol.circuit?.hadDefaultLanding) {
                    // @ts-ignore
                    const { id, ...defaultLand} = defaultLanding;
                    // @ts-ignore
                    transformedVol.landings = [{...defaultLand, complets: parseInt(vol.quantite) * parseInt(defaultLand.complets ?? 1, 10), touches: parseInt(vol.quantite) * parseInt(defaultLand.touches ?? 0, 10)}];
                  }
                }
            }
          } else {
            if (isDefinedAndNotVoid(vol.landings)) {
                const oldLandings = vol.landings.filter(l => l && l['@id']).map(l => l['@id']);
                transformedVol.landings = oldLandings;
            }
          }
          transformedVols.push(transformedVol);
        }
        const newData = {
            ...data,
            date: new Date((new Date(date)).setHours(12, 0, 0)),
            pilote: getFormattedValueForBackEnd(pilote),
            encadrant: getFormattedValueForBackEnd(encadrant),
            aeronef: getFormattedValueForBackEnd(aeronef),
            createdBy: getFormattedValueForBackEnd(createdBy),
            updatedBy: getFormattedValueForBackEnd(updatedBy),
            vols: transformedVols,
        };
        return newData;
    };

    return (
      // @ts-ignore
      <Edit transform={transform} actions={<PrestationEditActions />}>  
        <SimpleForm>
            <Alert severity="info" icon={<BuildIcon fontSize="small" />} sx={{ mb: 2, width: '100%' }}>
              <strong>Aéronef, horamètres et durée</strong> sont en lecture seule.
              Pour corriger l'horamètre, utilisez le bouton <em>"Corriger l'horamètre"</em> sur la fiche de la prestation.
              Pour changer d'aéronef, supprimez cette prestation et recréez-la avec le bon aéronef.
            </Alert>
            <DateInput source="date" />
            <TextInput source="aeronef.immatriculation" label="Aéronef" disabled />
            <FilteredPiloteInput pilotes={ pilotes } circuits={ circuits }/>
            <EncadrantInput pilotes={ pilotes } circuits={ circuits }/>
            <ArrayInput source="vols">
                <SimpleFormIterator disableReordering>
                  <Box display="flex" gap={ !clientWithOptions(client) ? 2 : 3 } flexWrap="wrap">
                    <Box flex={1}>
                      <ReferenceInput reference="circuits" source="circuit.@id" label="Circuit" filter={{ isAvailable: true }} />
                    </Box>
                    <OptionInput client={ client }/>
                    <Box flex={1}>
                      <NumberInput source="quantite" />
                    </Box>
                  </Box>
                  <Box>
                    <LandingsInput client={client} />
                  </Box>
                </SimpleFormIterator>
            </ArrayInput>
            <NumberInput source="horametreDepart" disabled />
            <NumberInput source="duree" disabled />
            <NumberInput source="horametreFin" disabled />
            <TextInput source="remarques" />
        </SimpleForm>
      </Edit>
    );
};
