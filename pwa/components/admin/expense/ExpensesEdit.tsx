import { ArrayInput, BooleanInput, DateInput, Edit, FileInput, NumberInput, SelectInput, SimpleFormIterator, useRecordContext } from "react-admin";
import { SimpleForm, TextInput } from "react-admin";
import { useSessionContext } from "../SessionContextProvider";
import { paymentMode, syncOdooDocument } from "../../../app/lib/client";
import { Box, Typography } from "@mui/material";
import { isDefined } from "../../../app/lib/utils";
import { useFormContext, useWatch } from "react-hook-form";
import { useEffect, useRef } from "react";
import { MyFileField } from "../shared/OdooDocumentField";
import SharedTvaSelectInput from "../shared/TvaSelectInput";

const TotalsWatcher = () => {
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

    const totalTTC = details
      .map((d: any) => parseFloat(d?.amount ?? 0) || 0)
      .reduce((acc: number, val: number) => acc + val, 0);

    const totalHT = details
      .map((d: any) => {
        const amt = parseFloat(d?.amount ?? 0) || 0;
        const tva = parseFloat(d?.tauxTva ?? 0) || 0;
        return tva > 0 ? amt / (1 + tva) : amt;
      })
      .reduce((acc: number, val: number) => acc + val, 0);

    const roundedHT = parseFloat(totalHT.toFixed(2));
    const prevTotalTTC = getValues("totalTTC");
    const prevTotalHT = getValues("totalHT");

    if (prevTotalTTC !== totalTTC) {
      setValue("totalTTC", totalTTC, { shouldDirty: true });
    }
    if (prevTotalHT !== roundedHT) {
      setValue("totalHT", roundedHT, { shouldDirty: true });
    }
  }, [details, setValue, getValues]);

  return (
    <NumberInput
      source="totalHT"
      label="Total HT (€)"
      helperText="Le montant HT est recalculé à partir des TVA par ligne. Vous pouvez le modifier manuellement (arrête le recalcul)."
      onChange={(e: any) => {
        manualEditedRef.current = true;
        const v = parseFloat(e?.target?.value);
        setValue("totalHT", Number.isFinite(v) ? v : 0, { shouldDirty: true });
      }}
    />
  );
};

export const ExpensesEdit = () => {
  const { session } = useSessionContext();
  const defaultDetails = [{ mode: '', amount: '' }];

  const transform = async (data: any) => {
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
        <Typography className="mt-4" variant="h6" gutterBottom>Modes de paiement</Typography>
        <ArrayInput source="details" label="" defaultValue={defaultDetails}>
          <SimpleFormIterator inline disableAdd={false} disableRemove={true}>
            <SelectInput source="mode" label="Mode" choices={paymentMode} />
            <NumberInput source="amount" label="Montant TTC (€)" />
            <SharedTvaSelectInput source="tauxTva" label="TVA" size="small" fullWidth={false} />
          </SimpleFormIterator>
        </ArrayInput>
        <NumberInput source="totalTTC" label="Total TTC (€)" readOnly />
        <TotalsWatcher />
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
