import { Box, Typography, ToggleButton, ToggleButtonGroup } from "@mui/material";
import { Create, SimpleForm, TextInput, NumberInput, SelectInput, DateInput, required, ArrayInput, SimpleFormIterator, FileInput, BooleanInput } from "react-admin";
import { paymentMode, syncOdooDocument } from "../../../app/lib/client";
import { useSessionContext } from "../SessionContextProvider";
import { useFormContext, useWatch } from "react-hook-form";
import { useEffect, useState } from "react";
import { isDefined } from "../../../app/lib/utils";
import { MyFileField } from "../shared/OdooDocumentField";
import SharedTvaSelectInput from "../shared/TvaSelectInput";

type SaisieMode = "ttc" | "ht";

const TotalsWatcher = ({ saisieMode }: { saisieMode: SaisieMode }) => {
  const { setValue } = useFormContext();
  const details = useWatch({ name: "details" }) || [];
  const [manualHT, setManualHT] = useState<boolean>(false);

  useEffect(() => {
    if (saisieMode === "ht") {
      const totalHT = details
        .map((d: any) => parseFloat(d?.amount || 0))
        .reduce((acc: number, val: number) => acc + val, 0);

      const totalTTC = details
        .map((d: any) => {
          const amt = parseFloat(d?.amount || 0);
          const tva = parseFloat(d?.tauxTva || 0);
          return tva > 0 ? amt * (1 + tva) : amt;
        })
        .reduce((acc: number, val: number) => acc + val, 0);

      setValue("totalTTC", parseFloat(totalTTC.toFixed(2)), { shouldValidate: true, shouldDirty: true });
      if (!manualHT) {
        setValue("totalHT", parseFloat(totalHT.toFixed(2)), { shouldValidate: true, shouldDirty: true });
      }
    } else {
      const totalTTC = details
        .map((d: any) => parseFloat(d?.amount || 0))
        .reduce((acc: number, val: number) => acc + val, 0);

      setValue("totalTTC", totalTTC, { shouldValidate: true, shouldDirty: true });

      if (!manualHT) {
        const totalHT = details
          .map((d: any) => {
            const amt = parseFloat(d?.amount || 0);
            const tva = parseFloat(d?.tauxTva || 0);
            return tva > 0 ? amt / (1 + tva) : amt;
          })
          .reduce((acc: number, val: number) => acc + val, 0);
        setValue("totalHT", parseFloat(totalHT.toFixed(2)), { shouldValidate: true, shouldDirty: true });
      }
    }
  }, [details, manualHT, setValue, saisieMode]);

  return (
    <NumberInput
      source="totalHT"
      label="Total HT (€)"
      helperText="Calculé automatiquement. Ajustable manuellement si nécessaire."
      onChange={(e: any) => {
        setManualHT(true);
        setValue("totalHT", parseFloat(e.target.value), { shouldValidate: true, shouldDirty: true });
      }}
    />
  );
};

export const ExpensesCreate = () => {
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
      const justificatif = await syncOdooDocument(doc, 'expense', null, session);
      return { ...data, document: justificatif };
    }
    return data;
  };

  return (
    <Create transform={transform} redirect="list">
      <SimpleForm>
        <DateInput source="date" defaultValue={new Date()} label="Date" validate={required()} />
        <TextInput source="beneficiaire" label="Bénéficiaire" validate={required()} />
        <TextInput source="libelle" label="Libellé" />
        <Box display="flex" alignItems="center" justifyContent="space-between" mt={2} mb={1}>
          <Typography variant="h6">Lignes de dépense</Typography>
          <ToggleButtonGroup
            value={saisieMode}
            exclusive
            onChange={(_, val) => val && setSaisieMode(val)}
            size="small"
          >
            <ToggleButton value="ttc" sx={{ textTransform: 'none', px: 2 }}>Saisie TTC</ToggleButton>
            <ToggleButton value="ht" sx={{ textTransform: 'none', px: 2 }}>Saisie HT</ToggleButton>
          </ToggleButtonGroup>
        </Box>
        <ArrayInput source="details" label="" defaultValue={defaultDetails}>
          <SimpleFormIterator inline disableAdd={false} disableRemove={true}>
            <SelectInput source="mode" label="Mode" choices={paymentMode} />
            <NumberInput
              source="amount"
              label={saisieMode === "ht" ? "Montant HT (€)" : "Montant TTC (€)"}
              validate={required()}
            />
            <SharedTvaSelectInput source="tauxTva" label="TVA" isCreate size="small" fullWidth={false} />
          </SimpleFormIterator>
        </ArrayInput>
        <NumberInput source="totalTTC" label="Total TTC (€)" readOnly />
        <TotalsWatcher saisieMode={saisieMode} />
        <Box display="flex" gap={2} flexWrap="nowrap" width="100%" sx={{ marginTop: '2em', marginBottom: '2em' }}>
          <Box flex={1}>
            <BooleanInput source="relatedToMaintenance" label="Spécifique à un entretien" fullWidth
              helperText="Si coché, cette dépense pourra être rattachée à un entretien" defaultValue={false}
            />
          </Box>
        </Box>
        <FileInput source="document" multiple={false} label="Justificatif">
          <MyFileField source="contentUrl" />
        </FileInput>
      </SimpleForm>
    </Create>
  );
};
