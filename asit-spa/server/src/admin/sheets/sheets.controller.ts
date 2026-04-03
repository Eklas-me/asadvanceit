import { 
  Controller, 
  Get, 
  Post, 
  Patch, 
  Delete, 
  Body, 
  Param, 
  UseGuards, 
  BadRequestException 
} from '@nestjs/common';
import { PrismaService } from '../../prisma/prisma.service';
import { JwtAuthGuard } from '../../auth/guards/jwt-auth.guard';
import { RolesGuard } from '../../auth/guards/roles.guard';
import { Roles } from '../../auth/decorators/roles.decorator';

@Controller('api/admin/sheets')
@UseGuards(JwtAuthGuard, RolesGuard)
@Roles('admin', 'superadmin')
export class SheetsController {
  constructor(private prisma: PrismaService) {}

  @Get()
  async findAll() {
    const sheets = await this.prisma.google_sheets.findMany({
      orderBy: [
        { order: 'asc' },
        { title: 'asc' }
      ]
    });
    return sheets.map(s => ({ ...s, id: s.id.toString() }));
  }

  @Post()
  async create(@Body() data: any) {
    const { title, url, icon, permission_type, shift, user_ids } = data;
    
    // Sluggify title
    const slug = title.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
    
    // Check if slug already exists
    const existing = await this.prisma.google_sheets.findUnique({
      where: { slug }
    });
    
    let finalSlug = slug;
    if (existing) {
       finalSlug = `${slug}_${Date.now()}`;
    }

    return this.prisma.$transaction(async (tx) => {
      const sheet = await tx.google_sheets.create({
        data: {
          title,
          url,
          icon: icon || 'fas fa-file-excel',
          slug: finalSlug,
          permission_type: permission_type || 'public',
          shift: permission_type === 'shift_based' ? shift : null,
          is_visible: true,
          order: 0,
        }
      });

      if (permission_type === 'specific_users' && Array.isArray(user_ids)) {
        await tx.google_sheet_user.createMany({
          data: user_ids.map(userId => ({
            google_sheet_id: sheet.id,
            user_id: BigInt(userId)
          }))
        });
      }

      return { ...sheet, id: sheet.id.toString() };
    });
  }

  @Patch(':id')
  async update(@Param('id') id: string, @Body() data: any) {
    const { title, url, icon, permission_type, shift, user_ids } = data;
    const sheetId = BigInt(id);

    return this.prisma.$transaction(async (tx) => {
      const sheet = await tx.google_sheets.update({
        where: { id: sheetId },
        data: {
          title,
          url,
          icon,
          permission_type,
          shift: permission_type === 'shift_based' ? shift : null,
        }
      });

      // Update specific users
      if (permission_type === 'specific_users' && Array.isArray(user_ids)) {
        // Delete old relations
        await tx.google_sheet_user.deleteMany({
          where: { google_sheet_id: sheetId }
        });
        
        // Add new relations
        await tx.google_sheet_user.createMany({
          data: user_ids.map(userId => ({
            google_sheet_id: sheetId,
            user_id: BigInt(userId)
          }))
        });
      } else {
        // Remove relations if not specific_users anymore
        await tx.google_sheet_user.deleteMany({
          where: { google_sheet_id: sheetId }
        });
      }

      return { ...sheet, id: sheet.id.toString() };
    });
  }

  @Delete(':id')
  async remove(@Param('id') id: string) {
    const deleted = await this.prisma.google_sheets.delete({
      where: { id: BigInt(id) }
    });
    return { ...deleted, id: deleted.id.toString() };
  }

  @Patch(':id/toggle')
  async toggleVisibility(@Param('id') id: string) {
    const sheet = await this.prisma.google_sheets.findUnique({
      where: { id: BigInt(id) }
    });
    
    if (!sheet) throw new BadRequestException('Sheet not found');

    const updated = await this.prisma.google_sheets.update({
      where: { id: BigInt(id) },
      data: { is_visible: !sheet.is_visible }
    });
    
    return { ...updated, id: updated.id.toString() };
  }

  @Patch(':id/order')
  async updateOrder(@Param('id') id: string, @Body('order') order: number) {
     const updated = await this.prisma.google_sheets.update({
         where: { id: BigInt(id) },
         data: { order }
     });
     return { ...updated, id: updated.id.toString() };
  }
}
