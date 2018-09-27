## LAB API

[Click here](http://lab.test.nascop.org/download_api) to get the below post routes as a **POSTMAN** collection complete with all the fields you need. Open this file using **POSTMAN**.


All links are **POST** requests. All post requests are validated using a header called **apikey**. The postman collection has already added them.
*Open the link in **POSTMAN** and then set the request to **POST** *
- Get data by request type [click here](http://lab.test.nascop.org/api/function)
>Fields
> - test, 1 for eid, 2 for vl **required**
> - start_date (required when *end_date* is set). It filters using the date collected.
> - end_date *(greater than or equal to **start_date**)*
> - date_dispatched_start (required when *date_dispatched_end* is set)
> - date_dispatched_end  *(greater than or equal to **date_dispatched_start**)*
> - patient_id *Either a single patient_id or a comma separated list of patient ids with no spaces. Patient id is hei number for eid and ccc number for vl*
> - facility_code *The five digit MFL code*
> - order_numbers *Either a single order number or a comma separated list of order numbers with no spaces.*
> - location *AMRS location*
> This link is paginated i.e. only 20 results at a time will be returned. The return data has a field called next_page_url and other links to help you get all the results. There is also other useful data such as the total results found.

- Post incomplete eid request [eid](http://lab.test.nascop.org/api/eid)
- Post complete eid request [eid](http://lab.test.nascop.org/api/eid_complete)
- Post incomplete vl request [vl](http://lab.test.nascop.org/api/vl)
- Post complete vl request [vl](http://lab.test.nascop.org/api/vl_complete)

---
For the last 4 links, the following fields are common to all
> - dob **required**
> - datecollected  **required**  *(greater than or equal to **dob**)*
> - patient_identifier  **required** (ccc_no for vl and hei number for eid)
> - mflCode  **required**
> - sex  **required** (1 for male, 2 for female, 3 for unknown)
> - lab *If the lab is not filled it will be set to the lab where the sample is being sent*

---
The following fields are common to complete requests 
>- receivedstatus **required**
>- datereceived **required**  *(greater than or equal to **datecollected**)*
>- rejectedreason *required if received status is 2*
>- datetested *required if received status is 1*  *(greater than or equal to **datereceived**)*
>- result *required if received status is 1*
>- datedispatched *required if received status is 1*  *(greater than or equal to **datetested**)*
>- specimenlabelID *for use by the lab*
>- editted *indicates whether the record is an update of an existing record*
` Editted may be removed so that editted records are sent to another route`

---
The following fields are common to eid requests
> - entry_point **required**
> - feeding **required**
> - spots **integer**
> - regimen **required**
> - mother_prophylaxis **required**
> - mother_age **integer**
> - ccc_no *(The ccc number of the mother)*
> - mother_last_result *(The most recent vl of the mother)*
> - pcrtype  **required**  *(**integer between 1 and 5**)*
> - redraw *(Fill with any non zero integer if the sample is a redraw)*
> - enrollment_ccc_no *(ccc number of the infant if he/she is already enrolled)*

---
The following fields are common to vl requests
> - initiation_date *(date inititated on treatment)*
> - prophylaxis **Required**
> - regimenline **Required**
> - sampletype **Required**
> - justification **Required**
> - pmtct *(**Required** if sex is **2** i.e. **female**. 3 is for none of the above)*


---
**NB:**
> `All date fields must follow the YYYY-MM-DD format and be less than or equal to today.`
> `All fields are case sensitive`



