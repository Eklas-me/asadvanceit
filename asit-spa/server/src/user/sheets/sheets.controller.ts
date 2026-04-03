import { 
  Controller, 
  Get, 
  Param, 
  UseGuards, 
  NotFoundException, 
  ForbiddenException, 
  Request 
} from '@nestjs/common';
import { PrismaService } from '../../prisma/prisma.service';
import { JwtAuthGuard } from '../../auth/guards/jwt-auth.guard';
import { users_role } from '@prisma/client';

@Controller('api/sheets')
@UseGuards(JwtAuthGuard)
export class SheetsController {
  constructor(private prisma: PrismaService) {}

  @Get()
  async findAll(@Request() req: any) {
    const user = req.user;
    
    // Admins see all visible sheets (plus can see all anyway)
    if (user.role === users_role.admin) {
      return {
          data: (await this.prisma.google_sheets.findMany({
            where: { is_visible: true },
            orderBy: [{ order: 'asc' }, { title: 'asc' }]
          })).map(s => ({ ...s, id: s.id.toString() }))
      };
    }

    // Normal users see only what they are permitted for
    const allVisible = await this.prisma.google_sheets.findMany({
      where: { is_visible: true },
      include: {
        google_sheet_user: true
      },
      orderBy: [{ order: 'asc' }, { title: 'asc' }]
    });

    const filtered = allVisible.filter(sheet => {
      if (sheet.permission_type === 'public') return true;
      if (sheet.permission_type === 'admin_only') return false;
      if (sheet.permission_type === 'shift_based') return sheet.shift === user.shift;
      if (sheet.permission_type === 'specific_users') {
        return sheet.google_sheet_user.some(su => su.user_id === BigInt(user.id));
      }
      return false;
    });

    return {
      data: filtered.map(({ google_sheet_user, ...rest }) => ({ 
        ...rest, 
        id: rest.id.toString() 
      }))
    };
  }

  @Get(':slug')
  async findOne(@Param('slug') slug: string, @Request() req: any) {
    const user = req.user;
    
    const sheet = await this.prisma.google_sheets.findUnique({
      where: { slug },
      include: {
        google_sheet_user: true
      }
    });

    if (!sheet || !sheet.is_visible) {
      throw new NotFoundException('Sheet not found');
    }

    // Permission check
    let hasAccess = false;
    if (user.role === users_role.admin) {
      hasAccess = true;
    } else {
      if (sheet.permission_type === 'public') hasAccess = true;
      else if (sheet.permission_type === 'shift_based') hasAccess = sheet.shift === user.shift;
      else if (sheet.permission_type === 'specific_users') {
        hasAccess = sheet.google_sheet_user.some(su => su.user_id === BigInt(user.id));
      }
    }

    if (!hasAccess) {
      throw new ForbiddenException('You do not have permission to access this sheet');
    }

    const { google_sheet_user, ...result } = sheet;
    return { ...result, id: result.id.toString() };
  }
}
