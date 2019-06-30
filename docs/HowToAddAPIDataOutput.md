# How to Add API Output in Data Processor

API output returns an API which can be called to retrieve the Data Processor results. 
In this tutorial we are going to add API Output to our Data Processor

Following the previous tutorials we have added Data Source and Data Fields to Data Processor

For this tutorial we are considering Contact as the Data Source and DisplayName, Contact ID as the output fields of the Data Processor

##### Follow the below mentioned steps to add API output:  

- First Navigate to your Data Processor and select **Add Output**
- Select **API** from the **Select Output** dropdown
- Enter the **API Entity Name**
- Enter the **API Action Name**

See the below screen for example:

![Screenshot API Output configuration screen](docs/images/apioutput_configuration.jpeg)

- Then click on Save button

##### Follow the below mentioned steps to see the usage of :
- Navigate to API Explorer v3 under Support -> Developer
- Seach for Entity name you entered while configuring API output (example: MyTestEntity)
- Click on Execute 
- This is the result of Data Processor which we can get by making API calls as shown.
