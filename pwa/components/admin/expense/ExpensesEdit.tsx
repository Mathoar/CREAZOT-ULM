import { ArrayInput, BooleanInput, DateInput, Edit, FileInput, NumberInput, SelectInput, SimpleFormIterator, useRecordContext } from "react-admin";
import { SimpleForm, TextInput } from "react-admin";
import { useSessionContext } from "../SessionContextProvider";
import { paymentMode, syncOdooDocument } from "../../../app/lib/client";
import { Box, Typography, ToggleButton, ToggleButtonGroup } from "@mui/material";
import { isDefined } from "../../../app/lib/utils";
import { useFormContext, useWatch } from "react-hook-form";
import { useEffect, useRef, useState } from "react";
import { MyFileField } from "../shared/OdooDocumentField";
import SharedTvaSelectInput from "../shared/TvaSelectInput";

type SaisieMode = "ttc" | "ht";

const TotalsWatcher = ({ saisieMode }: { saisieMode: SaisieMode }) => {
  const record = useRecordContext();
  const { setValue, getValues, control } = useFormContext();
  const details = useWatch({ name: "details", control }) || [];

  const initializedRef = useRef(false);
  const manualEditedRef = useRef(false);
  const skipNextAutoRecalcRef = useRef(false);

  useEffect(() => {
    if (!record || initializedRef.current) return;

    const t = setTimeout(() => {
      const current = getValues();
      let copiedSomething = false;

      if ((current.totalHT === undefined || current.totalHT === null || current.totalHT === "") && record.totalHT !== undefined) {
        setValue("totalHT", record.totalHT, { shouldDirty: false, shouldValidate: false });
        copiedSomething = true;
      }

      if ((current.totalTTC === undefined || current.totalTTC === null || current.totalTTC === "") && record.totalTTC !== undefined) {
        setValue("totalTTC", record.totalTTC, { shouldDirty: false, shouldValidate: false });
        copiedSomething = true;
      }

      skipNextAutoRecalcRef.current = copiedSomething;
      initializedRef.current = true;
    }, 0);

    return () => clearTimeout(t);
  }, [record, getValues, setValue]);

  useEffect(() => {
    if (!initializedRef.current) return;
    if (manualEditedRef.current) return;

    if (skipNextAutoRecalcRef.current) {
      skipNextAutoRecalcRef.current = false;
      return;
    }

    let totalTTC: number;
    let totalHT: number;

    if (saisieMode === "ht") {
      totalHT = details
        .map((d: any) => parseFloat(d?.amount ?? 0) || 0)
        .reduce((acc: number, val: number) => acc + val, 0);

      totalTTC = details
        .map((d: any) => {
          const amt = parseFloat(d?.amount ?? 0) || 0;
          const tva = parseFloat(d?.tauxTva ?? 0) || 0;
          return tva > 0 ? amt * (1 + tva) : amt;
        })
        .reduce((acc: number, val: number) => acc + val, 0);
    } else {
      totalTTC = details
        .map((d: any) => parseFloat(d?.amount ?? 0) || 0)
        .reduce((acc: number, val: number) => acc + val, 0);

      totalHT = details
        .map((d: any) => {
          const amt = parseFloat(d?.amount ?? 0) || 0;
          const tva = parseFloat(d?.tauxTva ?? 0) || 0;
          return tva > 0 ? amt / (1 + tva) : amt;
        })
        .reduce((acc: number, val: number) => acc + val, 0);
    }

    const roundedTTC = parseFloat(totalTTC.toFixed(2));
    const roundedHT = parseFloat(totalHT.toFixed(2));
    const prevTotalTTC = getValues("totalTTC");
    const prevTotalHT = getValues("totalHT");

    if (prevTotalTTC !== roundedTTC) {
      setValue("totalTTC", roundedTTC, { shouldDirty: true });
    }
    if (prevTotalHT !== roundedHT) {
      setValue("totalHT", roundedHT, { shouldDirty: true });
    }
  }, [details, setValue, getValues, saisieMode]);

  return (
    <NumberInput
      source="totalHT"
      label="Total HT (€)"
      helperText="Recalculé automatiquement. Modifiable manuellement (arrête le recalcul)."
      onChange={(e: any) => {
        manualEditedRef.current = true;
        const v = parseFloat(e?.target?.value);
        setValue("totalHT", Number.isFinite(v) ? v : 0, { shouldDirty: true });
      }}
    />
  );
};

const SaisieModeToggle = ({ saisieMode, setSaisieMode }: { saisieMode: SaisieMode; setSaisieMode: (m: SaisieMode) => void }) => {
  const { getValues, setValue } = useFormContext();

  const handleToggle = (_: any, newMode: SaisieMode | null) => {
    if (!newMode || newMode === saisieMode) return;

    const details = getValues("details") || [];
    const converted = details.map((d: any) => {
      const amt = parseFloat(d?.amount ?? 0) || 0;
      const tva = parseFloat(d?.tauxTva ?? 0) || 0;
      if (tva <= 0 || amt === 0) return d;

      const newAmount = newMode === "ht"
        ? parseFloat((amt / (1 + tva)).toFixed(2))
        : parseFloat((amt * (1 + tva)).toFixed(2));
      return { ...d, amount: newAmount };
    });

    setValue("details", converted, { shouldDirty: true });
    setSaisieMode(newMode);
  };

  return (
    <ToggleButtonGroup value={saisieMode} exclusive onChange={handleToggle} size="small">
      <ToggleButton value="ttc" sx={{ textTransform: 'none', px: 2 }}>Saisie TTC</ToggleButton>
      <ToggleButton value="ht" sx={{ textTransform: 'none', px: 2 }}>Saisie HT</ToggleButton>
    </ToggleButtonGroup>
  );
};

export const ExpensesEdit = () => {
  const { session } = useSessionContext();
  const [saisieMode, setSaisieMode] = useState<SaisieMode>("ttc");
  const defaultDetails = [{ mode: '', amount: '' }];

  const transform = async (data: any) => {
    if (saisieMode === "ht" && data.details) {
      const convertedDetails = data.details.map((d: any) => {
        const amt = parseFloat(d?.amount || 0);
        const tva = parseFloat(d?.tauxTva || 0);
        const amountTTC = tva > 0 ? parseFloat((amt * (1 + tva)).toFixed(2)) : amt;
        return { ...d, amount: amountTTC };
      });
      const totalTTC = convertedDetails.reduce((sum: number, d: any) => sum + (parseFloat(d.amount) || 0), 0);
      data = { ...data, details: convertedDetails, totalTTC: parseFloat(totalTTC.toFixed(2)) };
    }

    if (isDefined(data.document)) {
      const fileName = data?.document?.description || data?.document?.title || data?.document?.path || "Sans nom";
      const doc = data.document ? { ...data.document, description: fileName } : null;
      const justificatif = await syncOdooDocument(doc, 'expense', data.id, session);
      return { ...data, document: justificatif };
    }
    return data;
  };

  return (
    <Edit transform={transform} redirect="list">
      <SimpleForm>
        <DateInput source="date" defaultValue={new Date()} label="Date" />
        <TextInput source="beneficiaire" label="Bénéficiaire" />
        <TextInput source="libelle" label="Libellé" />
        <Box display="flex" alignItems="center" justifyContent="space-between" width="100%" mt={2} mb={1}>
          <Typography variant="h6" sx={{ flexShrink: 0 }}>Lignes de dépense</Typography>
          <SaisieModeToggle saisieMode={saisieMode} setSaisieMode={setSaisieMode} />
        </Box>
        <ArrayInput source="details" label="" defaultValue={defaultDetails}>
          <SimpleFormIterator inline disableAdd={false} disableRemove={true}>
            <SelectInput source="mode" label="Mode" choices={paymentMode} />
            <NumberInput
              source="amount"
              label={saisieMode === "ht" ? "Montant HT (€)" : "Montant TTC (€)"}
            />
            <SharedTvaSelectInput source="tauxTva" label="TVA" size="small" fullWidth={false} />
          </SimpleFormIterator>
        </ArrayInput>
        <NumberInput source="totalTTC" label="Total TTC (€)" readOnly />
        <TotalsWatcher saisieMode={saisieMode} />
        <Box display="flex" gap={2} flexWrap="nowrap" width="100%" sx={{ marginTop: '1.5em', marginBottom: '2em' }}>
          <Box flex={1}>
            <BooleanInput source="relatedToMaintenance" label="Spécifique à un entretien" fullWidth
              helperText="Si coché, cette dépense pourra être rattachée à un entretien"
            />
          </Box>
        </Box>
        <FileInput source="document" multiple={false} label="Justificatif">
          <MyFileField source="contentUrl" />
        </FileInput>
      </SimpleForm>
    </Edit>
  );
};
