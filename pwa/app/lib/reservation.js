export const status = [
    {id: "VALIDATED", name: "Validé"},
    {id: "WAITING", name: "En attente de confirmation"},
    {id: "WHEATER_REPORT", name:"Report météo"},
    {id: "PASSENGER_REPORT", name: "Report client"},
    {id: "INTERN_REPORT", name: "Report interne"},
    {id: "WHEATER_CANCEL", name:"Annulation météo"},
    {id: "PASSENGER_CANCEL", name: "Annulation client"},
    {id: "INTERN_CANCEL", name: "Annulation interne"}
];

export const positions = [
    {id: "Leader", name: "Leader"},
    {id: "2", name: "2"},
    {id: "3", name:"3"},
    {id: "4", name: "4"},
    {id: "-", name: "-"}
];

export const getPositionChoices = (aeronefCount) => {
    if (!aeronefCount || aeronefCount <= 1) return [{id: "-", name: "-"}];
    const choices = [{id: "Leader", name: "Leader"}];
    for (let i = 2; i <= aeronefCount; i++) {
        choices.push({id: String(i), name: String(i)});
    }
    choices.push({id: "-", name: "-"});
    return choices;
};