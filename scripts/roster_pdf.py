#!/usr/bin/env python3
"""
Edunex Class Roster PDF Generator
Reads JSON from stdin, produces PDF to stdout or file.
Usage: echo '{}' | python3 roster_pdf.py [--output FILE]
"""
import sys, os, json, argparse
from reportlab.lib import colors
from reportlab.lib.pagesizes import A4, landscape
from reportlab.lib.units import mm
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.platypus import (
    SimpleDocTemplate, Table, TableStyle, Paragraph, Spacer,
    Image as RLImage, PageBreak, KeepTogether
)
from reportlab.lib.enums import TA_CENTER, TA_LEFT, TA_RIGHT
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont

BASE = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
IMAGES = os.path.join(BASE, 'public', 'images')


def build_pdf(data, output_path=None):
    """Build the roster PDF from structured data."""
    courses = data['courses']
    roster = data['roster']
    homeroom = data['homeroom']
    stats = data['stats']
    doc_id = data.get('doc_id', 'EDU-' + __import__('datetime').datetime.now().strftime('%Y') + '-000001')
    stamp = data.get('stamp', __import__('datetime').datetime.now().strftime('%B %d, %Y'))

    page_w, page_h = landscape(A4)
    margin = 18 * mm

    # Styles
    styles = getSampleStyleSheet()
    s_title = ParagraphStyle('PTitle', parent=styles['Title'], fontSize=16, leading=20,
                              textColor=colors.HexColor('#1a1a2e'), spaceAfter=2 * mm,
                              alignment=TA_CENTER, fontName='Helvetica-Bold')
    s_subtitle = ParagraphStyle('PSub', parent=styles['Normal'], fontSize=10, leading=13,
                                 textColor=colors.HexColor('#64748b'), alignment=TA_CENTER,
                                 spaceAfter=4 * mm)
    s_section = ParagraphStyle('PSection', parent=styles['Normal'], fontSize=11, leading=14,
                                textColor=colors.HexColor('#6366f1'), fontName='Helvetica-Bold',
                                spaceBefore=4 * mm, spaceAfter=2 * mm)
    s_label = ParagraphStyle('PLabel', fontSize=7.5, fontName='Helvetica-Bold',
                              textColor=colors.HexColor('#1e293b'), alignment=TA_LEFT,
                              leading=9)
    s_value = ParagraphStyle('PValue', fontSize=7.5, fontName='Helvetica',
                              textColor=colors.HexColor('#475569'), alignment=TA_CENTER,
                              leading=9)
    s_cell = ParagraphStyle('PCell', fontSize=6.8, fontName='Helvetica',
                             textColor=colors.HexColor('#1e293b'), alignment=TA_CENTER,
                             leading=8)
    s_cell_left = ParagraphStyle('PCellL', fontSize=6.8, fontName='Helvetica',
                                  textColor=colors.HexColor('#1e293b'), alignment=TA_LEFT,
                                  leading=8)
    s_header = ParagraphStyle('PHead', fontSize=6.5, fontName='Helvetica-Bold',
                               textColor=colors.HexColor('#1e293b'), alignment=TA_CENTER,
                               leading=8)
    s_header_left = ParagraphStyle('PHeadL', fontSize=6.5, fontName='Helvetica-Bold',
                                    textColor=colors.HexColor('#1e293b'), alignment=TA_LEFT,
                                    leading=8)

    # ── Header + Footer on every page ──
    def on_page(canvas, doc):
        canvas.saveState()
        w, h = landscape(A4)
        mh = margin

        # Top line
        canvas.setStrokeColor(colors.HexColor('#1a1a2e'))
        canvas.setLineWidth(1.2)
        canvas.line(mh, h - 8 * mm, w - mh, h - 8 * mm)

        # Flag (top-left)
        flag_path = os.path.join(IMAGES, 'ethiopian-flag.jpeg')
        if os.path.exists(flag_path):
            canvas.drawImage(flag_path, mh, h - 18 * mm, width=14 * mm, height=10 * mm,
                           preserveAspectRatio=True, mask='auto')

        # Ministry logo (top-right)
        min_path = os.path.join(IMAGES, 'ministry-logo.png')
        if os.path.exists(min_path):
            canvas.drawImage(min_path, w - mh - 14 * mm, h - 18 * mm, width=14 * mm, height=14 * mm,
                           preserveAspectRatio=True, mask='auto')

        # Header text (center)
        canvas.setFont('Helvetica-Bold', 8)
        canvas.setFillColor(colors.HexColor('#1a1a2e'))
        canvas.drawCentredString(w / 2, h - 11 * mm, 'FEDERAL DEMOCRATIC REPUBLIC OF ETHIOPIA')
        canvas.setFont('Helvetica', 7)
        canvas.setFillColor(colors.HexColor('#64748b'))
        canvas.drawCentredString(w / 2, h - 15 * mm, 'Ministry of Education')

        # Doc ID
        canvas.setFont('Helvetica', 6.5)
        canvas.setFillColor(colors.HexColor('#64748b'))
        canvas.drawRightString(w - mh, h - 11 * mm, doc_id)

        # Footer line
        canvas.setStrokeColor(colors.HexColor('#d1d5db'))
        canvas.setLineWidth(0.5)
        canvas.line(mh, 12 * mm, w - mh, 12 * mm)

        # Footer text
        canvas.setFont('Helvetica-Bold', 6)
        canvas.setFillColor(colors.HexColor('#64748b'))
        canvas.drawString(mh, 8 * mm, 'EDUNEX LMS')
        canvas.setFont('Helvetica', 5.5)
        canvas.drawString(mh, 5 * mm, 'henockakriso.com  ·  GitHub @henokakriso  ·  ARWE-PL Licensed [' + str(__import__('datetime').datetime.now().year) + ']')
        canvas.drawCentredString(w / 2, 8 * mm, doc_id)
        canvas.drawRightString(w - mh, 8 * mm, f'Page {canvas.getPageNumber()}')

        # Watermark (very faded EDUNEX text, behind content)
        canvas.saveState()
        canvas.setFillColor(colors.Color(0.92, 0.92, 0.93))
        canvas.setFont('Helvetica-Bold', 80)
        canvas.drawCentredString(w / 2, h / 2, 'EDUNEX')
        canvas.setFont('Helvetica', 14)
        canvas.drawCentredString(w / 2, h / 2 - 20, 'www.edunex.com')
        canvas.restoreState()

        canvas.restoreState()

    def on_first_page(canvas, doc):
        on_page(canvas, doc)

    # Build elements
    elements = []

    # Title area
    elements.append(Spacer(1, 12 * mm))
    elements.append(Paragraph('CLASS ROSTER', s_title))
    elements.append(Paragraph(f'{homeroom["name"]}  —  {stamp}', s_subtitle))
    elements.append(Spacer(1, 4 * mm))

    # Summary box
    summary_data = [
        [Paragraph('<b>Class</b>', s_label), Paragraph(homeroom['name'], s_value),
         Paragraph('<b>Students</b>', s_label), Paragraph(str(stats['student_count']), s_value)],
        [Paragraph('<b>Subjects</b>', s_label), Paragraph(str(stats['subject_count']), s_value),
         Paragraph('<b>Class Average</b>', s_label), Paragraph(f'{stats["class_avg"]}%', s_value)],
        [Paragraph('<b>Pass Rate</b>', s_label), Paragraph(f'{stats["pass_rate"]}%', s_value),
         Paragraph('<b>Total Absences</b>', s_label), Paragraph(str(stats['total_absences']), s_value)],
    ]
    summary_table = Table(summary_data, colWidths=[70, 80, 80, 80])
    summary_table.setStyle(TableStyle([
        ('BOX', (0, 0), (-1, -1), 0.8, colors.HexColor('#d1d5db')),
        ('INNERGRID', (0, 0), (-1, -1), 0.3, colors.HexColor('#e5e7eb')),
        ('BACKGROUND', (0, 0), (0, -1), colors.HexColor('#f8fafc')),
        ('BACKGROUND', (2, 0), (2, -1), colors.HexColor('#f8fafc')),
        ('VALIGN', (0, 0), (-1, -1), 'MIDDLE'),
        ('TOPPADDING', (0, 0), (-1, -1), 3),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 3),
        ('LEFTPADDING', (0, 0), (-1, -1), 6),
        ('RIGHTPADDING', (0, 0), (-1, -1), 6),
    ]))
    elements.append(summary_table)
    elements.append(Spacer(1, 5 * mm))

    # Section header
    elements.append(Paragraph('GRADE REPORT', s_section))

    # Build subject labels
    sub_label = lambda c: (c.get('subject_name') or c.get('title', ''))[:3].upper()

    # Table header
    col_headers = ['#', 'Name', 'ID', 'Age', 'Sex', '']
    for c in courses:
        col_headers.append(sub_label(c))
    col_headers += ['Abs', 'Tot', 'Avg', 'Rank']

    # Column widths
    page_w_usable = page_w - 2 * margin
    roll_w = 16
    name_w = 72
    id_w = 48
    age_w = 18
    sex_w = 20
    period_w = 18
    abs_w = 16
    tot_w = 24
    avg_w = 24
    rank_w = 20
    fixed_w = roll_w + name_w + id_w + age_w + sex_w + period_w + abs_w + tot_w + avg_w + rank_w
    num_subj = len(courses)
    subj_w = max(18, (page_w_usable - fixed_w) / num_subj)

    col_widths = [roll_w, name_w, id_w, age_w, sex_w, period_w]
    col_widths += [subj_w] * num_subj
    col_widths += [abs_w, tot_w, avg_w, rank_w]

    # Build rows
    def fmt(v):
        if v is None: return '—'
        return f'{v:.1f}' if isinstance(v, float) else str(v)

    table_data = []

    # Header row
    header_row = []
    for i, h in enumerate(col_headers):
        st = s_header_left if i in (1, 2) else s_header
        header_row.append(Paragraph(h, st))
    table_data.append(header_row)

    # Data rows
    for ri, r in enumerate(roster):
        roll = ri + 1
        name = r['name']
        sid = r['sid']
        age = str(r.get('age') or '—')
        sex = r.get('gender') or '—'

        # FY row
        fy = [Paragraph(str(roll), s_cell), Paragraph(name, s_cell_left),
              Paragraph(sid, s_cell_left), Paragraph(age, s_cell),
              Paragraph(sex, s_cell), Paragraph('FY', s_cell)]
        for c in courses:
            sub = r.get('subjects', {}).get(str(c['id']), {})
            fy.append(Paragraph(fmt(sub.get('fy')), s_cell))
        fy.append(Paragraph(str(r.get('fy_absences', 0)), s_cell))
        fy.append(Paragraph(fmt(r.get('fy_total')), s_cell))
        fy.append(Paragraph(fmt(r.get('fy_average')), s_cell))
        fy.append(Paragraph(str(r.get('fy_rank', '')), s_cell))
        table_data.append(fy)

        # S2 row
        s2 = [Paragraph('', s_cell), Paragraph('', s_cell_left),
              Paragraph('', s_cell_left), Paragraph('', s_cell),
              Paragraph('', s_cell), Paragraph('S2', s_cell)]
        for c in courses:
            sub = r.get('subjects', {}).get(str(c['id']), {})
            s2.append(Paragraph(fmt(sub.get('s2')), s_cell))
        s2.append(Paragraph(str(r.get('s2_absences', 0)), s_cell))
        s2.append(Paragraph(fmt(r.get('s2_total')), s_cell))
        s2.append(Paragraph(fmt(r.get('s2_average')), s_cell))
        s2.append(Paragraph(str(r.get('s2_rank', '')), s_cell))
        table_data.append(s2)

        # Avg row
        avg = [Paragraph('', s_cell), Paragraph('', s_cell_left),
               Paragraph('', s_cell_left), Paragraph('', s_cell),
               Paragraph('', s_cell), Paragraph('Avg', s_cell)]
        for c in courses:
            sub = r.get('subjects', {}).get(str(c['id']), {})
            avg.append(Paragraph(fmt(sub.get('avg')), s_cell))
        avg.append(Paragraph(str(r.get('avg_absences', 0)), s_cell))
        avg.append(Paragraph(fmt(r.get('avg_total')), s_cell))
        avg.append(Paragraph(fmt(r.get('avg_average')), s_cell))
        avg.append(Paragraph(str(r.get('avg_rank', '')), s_cell))
        table_data.append(avg)

    # Build table
    roster_table = Table(table_data, colWidths=col_widths, repeatRows=1)

    # Table style
    ts = [
        # Header
        ('BACKGROUND', (0, 0), (-1, 0), colors.HexColor('#6366f1')),
        ('TEXTCOLOR', (0, 0), (-1, 0), colors.white),
        ('FONTNAME', (0, 0), (-1, 0), 'Helvetica-Bold'),
        ('FONTSIZE', (0, 0), (-1, 0), 6.5),
        ('ALIGN', (0, 0), (-1, 0), 'CENTER'),
        ('BOTTOMPADDING', (0, 0), (-1, 0), 4),
        ('TOPPADDING', (0, 0), (-1, 0), 4),

        # All cells
        ('VALIGN', (0, 0), (-1, -1), 'MIDDLE'),
        ('TOPPADDING', (0, 1), (-1, -1), 2),
        ('BOTTOMPADDING', (0, 1), (-1, -1), 2),
        ('LEFTPADDING', (0, 0), (-1, -1), 2),
        ('RIGHTPADDING', (0, 0), (-1, -1), 2),

        # Grid
        ('GRID', (0, 0), (-1, -1), 0.4, colors.HexColor('#d1d5db')),
        ('LINEBELOW', (0, 0), (-1, 0), 1, colors.HexColor('#4f46e5')),

        # Alignment
        ('ALIGN', (0, 1), (0, -1), 'CENTER'),   # #
        ('ALIGN', (1, 1), (1, -1), 'LEFT'),      # Name
        ('ALIGN', (2, 1), (2, -1), 'LEFT'),      # ID
        ('ALIGN', (3, 1), (3, -1), 'CENTER'),    # Age
        ('ALIGN', (4, 1), (4, -1), 'CENTER'),    # Sex
        ('ALIGN', (5, 1), (5, -1), 'CENTER'),    # Period
        ('ALIGN', (6, 1), (-2, -1), 'CENTER'),   # Subjects + Abs/Tot/Avg/Rank
        ('ALIGN', (-1, 1), (-1, -1), 'CENTER'),  # Rank
    ]

    # Alternating row colors per student group (3 rows each)
    for ri in range(len(roster)):
        base = 1 + ri * 3
        if ri % 2 == 1:
            ts.append(('BACKGROUND', (0, base), (-1, base + 2), colors.HexColor('#f8fafc')))
        # Light separator between student groups
        if ri < len(roster) - 1:
            ts.append(('LINEBELOW', (0, base + 2), (-1, base + 2), 0.6, colors.HexColor('#94a3b8')))

    roster_table.setStyle(TableStyle(ts))
    elements.append(roster_table)

    # Build PDF
    if output_path:
        doc = SimpleDocTemplate(
            output_path, pagesize=landscape(A4),
            leftMargin=margin, rightMargin=margin,
            topMargin=22 * mm, bottomMargin=18 * mm
        )
        doc.build(elements, onFirstPage=on_first_page, onLaterPages=on_page)
        return output_path
    else:
        # Write to stdout
        import io
        buf = io.BytesIO()
        doc = SimpleDocTemplate(
            buf, pagesize=landscape(A4),
            leftMargin=margin, rightMargin=margin,
            topMargin=22 * mm, bottomMargin=18 * mm
        )
        doc.build(elements, onFirstPage=on_first_page, onLaterPages=on_page)
        sys.stdout.buffer.write(buf.getvalue())
        return None


def main():
    parser = argparse.ArgumentParser(description='Edunex Roster PDF Generator')
    parser.add_argument('--output', '-o', help='Output file path (else stdout)')
    parser.add_argument('--input', '-i', help='Input JSON file (else stdin)')
    args = parser.parse_args()

    if args.input:
        with open(args.input) as f:
            data = json.load(f)
    else:
        data = json.load(sys.stdin)

    build_pdf(data, args.output)


if __name__ == '__main__':
    main()
