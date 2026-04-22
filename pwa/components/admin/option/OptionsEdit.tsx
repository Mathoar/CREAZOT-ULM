import { SimpleForm, TextInput, NumberInput, Edit, required, BooleanInput } from "react-admin";
import { Box } from "@mui/material";
import TvaSelectInput from "../shared/TvaSelectInput";

export const OptionsEdit = () => {

  return (
    <Edit>
        <SimpleForm>
            <TextInput source="nom" label="Nom de l'option" validate={required()}/>
            <Box display="flex" gap={2} flexWrap="nowrap" width="100%">
              <Box flex={1}>
                <NumberInput source="prix" label="Prix TTC (€)" validate={required()}/>
              </Box>
              <Box flex={1}>
                <TvaSelectInput source="tauxTva" label="TVA" />
              </Box>
            </Box>
            <BooleanInput source="isAvailable" label="Disponible" />
        </SimpleForm>
    </Edit>
  )
};
