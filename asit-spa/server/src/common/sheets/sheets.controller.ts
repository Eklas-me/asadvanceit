import { Controller, Get, UseGuards, Request } from '@nestjs/common';
import { PrismaService } from '../../prisma/prisma.service';
import { JwtAuthGuard } from '../../auth/guards/jwt-auth.guard';

@Controller('api/sheets')
@UseGuards(JwtAuthGuard)
export class SheetsController {
  constructor(private prisma: PrismaService) {}

  @Get()
  async getSheets(@Request() req: any) {
    const user = req.user;
    
    // If admin, they see all visible sheets
    if (user.role === 'admin') {
      const sheets = await this.prisma.google_sheets.findMany({
        where: { is_visible: true },
        orderBy: { order: 'asc' }
      });
      return sheets.map(s => ({ ...s, id: s.id.toString() }));
    }

    // Else find user specific sheets
    // 1. Public sheets
    // 2. Shift based sheets
    // 3. User specific sheets

    const [publicSheets, shiftSheets, userSpecificSheets] = await Promise.all([
      this.prisma.google_sheets.findMany({
        where: { is_visible: true, permission_type: 'public' }
      }),
      user.shift ? this.prisma.google_sheets.findMany({
        where: { is_visible: true, permission_type: 'shift_based', shift: user.shift }
      }) : Promise.resolve([]),
      this.prisma.google_sheet_user.findMany({
        where: { user_id: BigInt(user.userId) },
        include: { google_sheets: true }
      })
    ]);

    const specificSheets = userSpecificSheets
      .map(usu => usu.google_sheets)
      .filter(s => s.is_visible);

    // Combine and deduplicate
    const allSheets = [...publicSheets, ...shiftSheets, ...specificSheets];
    const uniqueIds = new Set();
    const finalSheets: any[] = [];

    for (const sheet of allSheets) {
      const idStr = sheet.id.toString();
      if (!uniqueIds.has(idStr)) {
        uniqueIds.add(idStr);
        finalSheets.push({ ...sheet, id: idStr });
      }
    }

    // Sort by order manually
    finalSheets.sort((a, b) => a.order - b.order);

    return finalSheets;
  }
}
