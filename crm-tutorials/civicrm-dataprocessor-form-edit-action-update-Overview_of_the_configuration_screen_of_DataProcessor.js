{
    "title": ts("Overview of the configuration screen of DataProcessor"),
    "auto_start": true,
    "steps": [
        {
            "target": "#DataProcessor .label",
            "title": ts("Contains the Title and Description of the DataProcessor"),
            "placement": "bottom",
            "content": ts("You can change the title and description of your dataprocessor.\nYou can also disable or enable the dataprocessor. "),
            "icon": ""
        },
        {
            "target": "#DataProcessor .add.button",
            "title": ts("Data Sources"),
            "placement": "bottom",
            "content": ts("A data processor has at least one data source.\nA data source could be CiviCRM entity, such as Individual, Household, Activity, Relationship etc. "),
            "icon": ""
        },
        {
            "target": "#DataProcessor .right.nowrap",
            "title": ts("Configuration of Data Sources"),
            "placement": "bottom",
            "content": ts("You can edit the Data Sources or remove them."),
            "icon": ""
        },
        {
            "target": "#DataProcessor .add.button:eq(3)",
            "title": ts("Fields"),
            "placement": "bottom",
            "content": ts("Select the fields you want to return in the results. For example Contact ID, First Name, Birth date, Gender etc."),
            "icon": ""
        },
        {
            "target": "#DataProcessor .order-icon:eq(17)",
            "title": ts("Change The order of the fields"),
            "placement": "bottom",
            "content": ts("You can use the arrows to change the order of the fields, the result will be shown in up to down order."),
            "icon": ""
        },
        {
            "target": "#DataProcessor .right.nowrap:eq(5)",
            "title": ts("<span style=\"font-size: 13.376px;\">Configuration of Fields</span>"),
            "placement": "bottom",
            "content": ts("Similarly to the conifguration of data sources. You can change the configuration of fields or remove them."),
            "icon": ""
        },
        {
            "target": "#DataProcessor .add.button:eq(1)",
            "title": ts("Aggregation Fields"),
            "placement": "bottom",
            "content": ts("Allows to group results by defining the aggregation field."),
            "icon": ""
        },
        {
            "target": "#DataProcessor .add.button:eq(4)",
            "title": ts("Filters"),
            "placement": "bottom",
            "content": ts("Define the filters, that may or may not (depends on configuration) be exposed to user on the output page. Filters can be defined so that user can narrow down the results according to his needs. "),
            "icon": ""
        },
        {
            "target": "#DataProcessor .add.button:eq(2)",
            "title": ts("Output result of Data Processor"),
            "placement": "bottom",
            "content": ts("Add output of the data processor. Output can be of form Contact Search, Activity Search etc. Depends on the user."),
            "icon": ""
        },
        {
            "target": "#_qf_DataProcessor_next-bottom",
            "title": ts("Save Data Processor Configuration"),
            "placement": "bottom",
            "content": "",
            "icon": ""
        },
        {
            "target": "#_qf_DataProcessor_cancel-bottom",
            "title": ts("<span style=\"font-size: 13.376px;\">Cancel Data Processor Configuration</span>"),
            "placement": "bottom",
            "content": "",
            "icon": ""
        }
    ],
    "groups": [],
    "url": "civicrm/dataprocessor/form/edit?action=update",
    "domain": null,
    "source": null
}
