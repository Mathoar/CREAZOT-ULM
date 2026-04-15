import {
  Edit,
  SimpleForm,
  TextInput,
  BooleanInput,
  NumberInput,
  CheckboxGroupInput,
  ReferenceManyField,
  Datagrid,
  ReferenceField,
  TextField,
  NumberField,
  EditButton,
  required,
  useRecordContext,
  Button,
} from "react-admin";
import { Link } from "react-router-dom";
import AddIcon from "@mui/icons-material/Add";
import { Typography, Divider } from "@mui/material";
import { useModuleChoices } from "./useModuleChoices";

const AddPriceButton = () => {
  const record = useRecordContext();
  if (!record) return null;
  return (
    <Button
      component={Link}
      to={`/module-pack-prices/create?modulePack=${encodeURIComponent(record["@id"] || record.id)}`}
      label="Ajouter un prix"
      startIcon={<AddIcon />}
    />
  );
};

export const ModulePacksEdit = () => {
  const { choices, loading } = useModuleChoices();

  return (
    <Edit>
      <SimpleForm>
        <TextInput source="name" label="Nom" validate={required()} />
        <TextInput source="slug" label="Slug" validate={required()} />
        <TextInput source="description" label="Description" multiline />
        <BooleanInput source="isDefault" label="Pack par défaut" />
        <NumberInput source="sortOrder" label="Ordre d'affichage" />
        <CheckboxGroupInput source="modules" label="Modules inclus" choices={choices} helperText={loading ? "Chargement des modules..." : false} />

        <Divider sx={{ mt: 3, mb: 2, width: "100%" }} />
        <Typography variant="h6" gutterBottom>
          Prix par grille tarifaire
        </Typography>

        <ReferenceManyField reference="module-pack-prices" target="modulePack" label="">
          <Datagrid sx={{ '& .RaDatagrid-headerCell': { backgroundColor: '#ededed', fontWeight: "lighter" } }}>
            <ReferenceField source="pricingCategory" reference="pricing-categories" label="Grille" link="edit">
              <TextField source="name" />
            </ReferenceField>
            <NumberField source="monthlyPrice" label="€/mois" options={{ style: 'currency', currency: 'EUR' }} />
            <EditButton />
          </Datagrid>
        </ReferenceManyField>

        <AddPriceButton />
      </SimpleForm>
    </Edit>
  );
};
