"""Create a clearly fictional report for localhost UI/document-link previews."""
from pathlib import Path
from reportlab.lib import colors
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle
from pypdf import PdfReader

destination = Path(__file__).resolve().parents[1] / "output/pdf/foxfire-local-demo-coa.pdf"
destination.parent.mkdir(parents=True, exist_ok=True)
orange, green, black = map(colors.HexColor, ("#FF5800", "#AFF769", "#15171A"))
body = ParagraphStyle("body", fontName="Helvetica", fontSize=11, leading=17, textColor=black)
heading = ParagraphStyle("heading", parent=body, fontName="Helvetica-Bold", fontSize=23, leading=28)
small = ParagraphStyle("small", parent=body, fontSize=10, leading=15)

def paragraph(text, style=body):
    return Paragraph(text, style)

def footer(canvas, doc):
    canvas.setFillColor(black)
    canvas.setFont("Helvetica", 9)
    canvas.drawString(42, 30, "LOCAL DEMO ONLY | Not a Certificate of Analysis | Page 1")

document = SimpleDocTemplate(str(destination), pagesize=A4, rightMargin=42, leftMargin=42,
                             topMargin=42, bottomMargin=55, title="LOCAL DEMO - Not a real COA",
                             author="Local Foxfire UI preview")
banner = Table([[paragraph("<b>LOCAL DEMO ONLY - NO REAL TEST RESULTS</b>")]], colWidths=[511])
banner.setStyle(TableStyle([("BACKGROUND", (0,0), (-1,-1), green),
                           ("LEFTPADDING", (0,0), (-1,-1), 14),
                           ("TOPPADDING", (0,0), (-1,-1), 12),
                           ("BOTTOMPADDING", (0,0), (-1,-1), 12)]))
story = [banner, Spacer(1, 25), paragraph("Sample Batch Document", heading), Spacer(1, 14),
         paragraph("This fictional file demonstrates how a customer opens an available report from the Testing &amp; COAs directory. It is not issued by a laboratory and is not evidence of product identity, purity, safety, or suitability."),
         Spacer(1, 24), paragraph("<b>Preview references</b>"), Spacer(1, 10)]
rows = [[paragraph("<b>Product</b>", small), paragraph("<b>Local demo batch</b>", small), paragraph("<b>Preview status</b>", small)]]
for product, batch, status in [("5-Amino-1MQ", "DEMO-LOCAL-156", "Report Available"),
                              ("GHK-Cu", "DEMO-LOCAL-27", "On File"),
                              ("KLOW Blend", "DEMO-LOCAL-110", "Archived")]:
    rows.append([paragraph(product, small), paragraph(batch, small), paragraph(status, small)])
table = Table(rows, colWidths=[163, 177, 171])
table.setStyle(TableStyle([("BACKGROUND", (0,0), (-1,0), colors.HexColor("#F4F4F4")),
                          ("VALIGN", (0,0), (-1,-1), "TOP"),
                          ("LINEBELOW", (0,0), (-1,-1), .5, colors.HexColor("#DDDDDD")),
                          ("LEFTPADDING", (0,0), (-1,-1), 10),
                          ("TOPPADDING", (0,0), (-1,-1), 11),
                          ("BOTTOMPADDING", (0,0), (-1,-1), 11)]))
story.extend([table, Spacer(1, 24), paragraph("<b>No analytical measurements are provided.</b>"),
              Spacer(1, 8), paragraph("Laboratory: not applicable. Test methods: not performed. Purity and identity: not measured. These statuses demonstrate website workflow only."),
              Spacer(1, 24), paragraph("<b>Before staging or production</b>"), Spacer(1, 8),
              paragraph("Restore the original local product fields and exclude this file from migration. Only associate approved real reports with the matching product and actual batch/lot identifier. Do not publish or rely on this sample as a real COA.")])
document.build(story, onFirstPage=footer)
reader = PdfReader(destination)
assert len(reader.pages) == 1
assert "NO REAL TEST RESULTS" in reader.pages[0].extract_text()
print(destination)
