import { Controller, Get, Post, Body, Patch, Param, Delete, UseGuards, Query } from '@nestjs/common';
import { PrismaService } from '../../prisma/prisma.service';
import { JwtAuthGuard } from '../../auth/guards/jwt-auth.guard';
import { RolesGuard } from '../../auth/guards/roles.guard';
import { Roles } from '../../auth/decorators/roles.decorator';

import * as bcrypt from 'bcrypt';

@Controller('api/admin/workers')
@UseGuards(JwtAuthGuard, RolesGuard)
@Roles('admin')
export class WorkersController {
  constructor(private prisma: PrismaService) {}

  @Post()
  async create(@Body() body: any) {
    const { password, ...rest } = body;
    const hashedPassword = await bcrypt.hash(password || '12345678', 10);
    
    const worker = await this.prisma.users.create({
      data: {
        ...rest,
        password: hashedPassword,
        status: rest.status || 'active',
        role: rest.role as any || 'user',
        gender: rest.gender as any || null
      }
    });

    return { data: { ...worker, id: worker.id.toString() } };
  }

  @Get('stats')
  async getStats() {
    const [total, active, suspended, rejected, legacy] = await Promise.all([
      this.prisma.users.count({ where: { role: 'user' } }),
      this.prisma.users.count({ where: { role: 'user', status: 'active' } }),
      this.prisma.users.count({ where: { role: 'user', status: 'suspended' } }),
      this.prisma.users.count({ where: { role: 'user', status: 'rejected' } }),
      this.prisma.users.count({ where: { role: 'user', needs_password_upgrade: true } }),
    ]);

    return {
      data: {
        total,
        active,
        suspended,
        rejected,
        legacy
      }
    };
  }

  @Get()
  async findAll(@Query('search') search = '', @Query('status') status?: string) {
    const where: any = {};
    if (search) where.name = { contains: search };
    if (status) where.status = status;

    const workers = await this.prisma.users.findMany({
      where,
      orderBy: { created_at: 'desc' }
    });
    return { data: workers.map(w => ({ ...w, id: w.id.toString() })) };
  }

  @Get(':id')
  async findOne(@Param('id') id: string) {
    const worker = await this.prisma.users.findUnique({
      where: { id: BigInt(id) }
    });
    if (!worker) {
      return null;
    }
    return { data: { ...worker, id: worker.id.toString() } };
  }

  @Patch(':id')
  async update(@Param('id') id: string, @Body() body: any) {
    const worker = await this.prisma.users.update({
      where: { id: BigInt(id) },
      data: {
        ...body,
        gender: body.gender as any
      }
    });
    return { data: { ...worker, id: worker.id.toString() } };
  }

  @Patch(':id/status')
  async updateStatus(@Param('id') id: string, @Body('status') status: string) {
    const worker = await this.prisma.users.update({
      where: { id: BigInt(id) },
      data: { status }
    });
    return { data: { ...worker, id: worker.id.toString() } };
  }

  @Delete(':id')
  async remove(@Param('id') id: string) {
    await this.prisma.users.delete({
      where: { id: BigInt(id) }
    });
    return { success: true };
  }

  @Patch('bulk/status')
  async bulkUpdateStatus(@Body() body: { ids: string[], status: string }) {
    await this.prisma.users.updateMany({
      where: { 
        id: { in: body.ids.map(id => BigInt(id)) },
        role: 'user' // Security: only affect workers
      },
      data: { status: body.status }
    });
    return { success: true };
  }

  @Post('bulk/delete')
  async bulkRemove(@Body() body: { ids: string[] }) {
    await this.prisma.users.deleteMany({
      where: { 
        id: { in: body.ids.map(id => BigInt(id)) },
        role: 'user' // Security: only affect workers
      }
    });
    return { success: true };
  }
}
