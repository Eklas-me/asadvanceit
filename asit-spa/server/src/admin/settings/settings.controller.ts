import { Controller, Get, Post, Body, Patch, UseGuards } from '@nestjs/common';
import { PrismaService } from '../../prisma/prisma.service';
import { JwtAuthGuard } from '../../auth/guards/jwt-auth.guard';
import { RolesGuard } from '../../auth/guards/roles.guard';
import { Roles } from '../../auth/decorators/roles.decorator';

@Controller('api/admin/settings')
@UseGuards(JwtAuthGuard, RolesGuard)
@Roles('admin')
export class SettingsController {
  constructor(private prisma: PrismaService) {}

  @Get()
  async findAll() {
    return this.prisma.site_settings.findMany();
  }

  @Post()
  async update(@Body() body: { key: string; value: string }) {
    return this.prisma.site_settings.upsert({
      where: { key: body.key },
      update: { value: body.value },
      create: { key: body.key, value: body.value }
    });
  }
}
