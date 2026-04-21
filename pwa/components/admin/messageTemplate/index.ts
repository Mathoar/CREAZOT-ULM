import { MessageTemplateList } from "./MessageTemplateList";
import { MessageTemplateEdit } from "./MessageTemplateEdit";
import { MessageTemplateCreate } from "./MessageTemplateCreate";

const messageTemplateResourceProps = {
  list: MessageTemplateList,
  edit: MessageTemplateEdit,
  create: MessageTemplateCreate,
  options: { label: "Modèles de message" },
};

export default messageTemplateResourceProps;
