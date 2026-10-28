from docx import Document
from docx.oxml import OxmlElement
from docx.oxml.ns import qn

src = r'C:\xampp\htdocs\Gourmetica\Manual_Usuario_Gourmetica.docx'
tmp = r'C:\xampp\htdocs\Gourmetica\Manual_Usuario_Gourmetica_table_fixed.docx'
doc = Document(src)
for table in doc.tables:
    if table.rows:
        trPr = table.rows[0]._tr.get_or_add_trPr()
        if trPr.find(qn('w:tblHeader')) is None:
            trPr.append(OxmlElement('w:tblHeader'))
doc.save(tmp)
print(tmp)
